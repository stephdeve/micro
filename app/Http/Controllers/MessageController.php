<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use App\Models\MessageAttachment;
use App\Models\MessageRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Interface de messagerie avec conversations
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Récupérer les conversations de l'utilisateur
        $conversations = Conversation::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['members', 'lastMessage'])
        ->withCount(['messages as unread_count' => function ($query) use ($user) {
            $query->where('sender_id', '!=', $user->id)
              ->whereDoesntHave('recipients', function ($q) use ($user) {
                  $q->where('user_id', $user->id)->whereNotNull('read_at');
              });
        }])
        ->orderByDesc(
            Message::select('created_at')
                ->whereColumn('conversation_id', 'conversations.id')
                ->latest()
                ->take(1)
        )
        ->get();

        // Conversation sélectionnée
        $activeConversation = null;
        $messages = collect();
        
        if ($request->has('conversation')) {
            $activeConversation = Conversation::findOrFail($request->conversation);
            
            // Vérifier que l'utilisateur est membre
            if (!$activeConversation->members->contains($user->id)) {
                abort(403, 'Vous n\'êtes pas membre de cette conversation');
            }
            
            // Marquer les messages comme lus
            $this->markConversationAsRead($activeConversation, $user->id);
            
            // Récupérer les messages déchiffrés
            $messages = $activeConversation->messages()
                ->with(['sender', 'attachments'])
                ->orderBy('created_at', 'asc')
                ->paginate(50);
                
            // Déchiffrer les messages pour l'affichage
            $messages->getCollection()->transform(function ($message) {
                $message->decrypted_body = $message->decrypt();
                return $message;
            });
        }

        // Liste des utilisateurs pour nouvelle conversation
        $users = User::where('id', '!=', $user->id)
            ->where('est_actif', true)
            ->orderBy('name')
            ->get();

        // Compteur de messages non lus total
        $totalUnread = MessageRecipient::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return view('messagerie.index', compact(
            'conversations', 
            'activeConversation', 
            'messages', 
            'users',
            'totalUnread'
        ));
    }

    /**
     * Créer une nouvelle conversation privée
     */
    public function createConversation(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|not_in:' . Auth::id(),
        ]);

        $user = Auth::user();
        $otherUser = User::findOrFail($validated['user_id']);

        // Vérifier si une conversation privée existe déjà
        $existingConversation = Conversation::where('is_group', false)
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->whereHas('members', function ($query) use ($otherUser) {
                $query->where('users.id', $otherUser->id);
            })
            ->first();

        if ($existingConversation) {
            return redirect()->route('messagerie.index', ['conversation' => $existingConversation->id]);
        }

        // Créer une nouvelle conversation
        $conversation = Conversation::create([
            'created_by' => $user->id,
            'is_group' => false,
        ]);

        // Ajouter les membres
        $conversation->members()->attach([$user->id, $otherUser->id], ['joined_at' => now()]);

        return redirect()->route('messagerie.index', ['conversation' => $conversation->id])
            ->with('success', 'Conversation démarrée avec ' . $otherUser->name);
    }

    /**
     * Créer un groupe
     */
    public function createGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'members' => 'required|array|min:1',
            'members.*' => 'exists:users,id',
        ]);

        $user = Auth::user();

        $conversation = Conversation::create([
            'name' => $validated['name'],
            'created_by' => $user->id,
            'is_group' => true,
        ]);

        // Ajouter le créateur comme admin
        $conversation->members()->attach($user->id, [
            'role' => 'admin',
            'joined_at' => now()
        ]);

        // Ajouter les autres membres
        if (!empty($validated['members'])) {
            $conversation->members()->attach($validated['members'], [
                'role' => 'member',
                'joined_at' => now()
            ]);
        }

        return redirect()->route('messagerie.index', ['conversation' => $conversation->id])
            ->with('success', 'Groupe "' . $validated['name'] . '" créé');
    }

    /**
     * Envoyer un message (chiffré AES-256)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content' => 'required|string|max:5000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // 10MB max
        ]);

        $user = Auth::user();
        $conversation = Conversation::findOrFail($validated['conversation_id']);

        // Vérifier que l'utilisateur est membre
        if (!$conversation->members->contains($user->id)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Chiffrer le message avec AES-256
        $encrypted = Message::encrypt($validated['content']);

        // Créer le message
        $message = Message::create([
            'sender_id' => $user->id,
            'conversation_id' => $conversation->id,
            'encrypted_content' => $encrypted['content'],
            'encryption_iv' => $encrypted['iv'],
            'encryption_key_hash' => $encrypted['key_hash'],
            'is_group_message' => $conversation->is_group,
            'is_encrypted' => true,
            'message_type' => 'text',
        ]);

        // Ajouter les destinataires
        $recipients = $conversation->members
            ->where('id', '!=', $user->id)
            ->pluck('id')
            ->toArray();

        foreach ($recipients as $recipientId) {
            MessageRecipient::create([
                'message_id' => $message->id,
                'user_id' => $recipientId,
            ]);
        }

        // Traiter les pièces jointes
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $this->handleAttachment($message, $file);
            }
        }

        // Charger les relations pour la réponse
        $message->load(['sender', 'attachments']);
        $message->decrypted_body = $validated['content']; // On a déjà le contenu en clair

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'html' => view('messagerie.partials.message', compact('message'))->render()
            ]);
        }

        return redirect()->route('messagerie.index', ['conversation' => $conversation->id]);
    }

    /**
     * Gérer une pièce jointe
     */
    private function handleAttachment(Message $message, $file): void
    {
        $originalName = $file->getClientOriginalName();
        $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = 'messages/' . $message->conversation_id;

        // Stocker le fichier
        Storage::disk('local')->putFileAs($path, $file, $storedName);

        // Créer l'enregistrement
        MessageAttachment::create([
            'message_id' => $message->id,
            'original_filename' => $originalName,
            'stored_filename' => $storedName,
            'file_path' => $path . '/' . $storedName,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_hash' => hash_file('sha256', $file->getRealPath()),
            'is_encrypted' => false, // Les fichiers ne sont pas chiffrés par défaut
        ]);

        // Mettre à jour le type de message
        $message->update(['message_type' => 'file']);
    }

    /**
     * Télécharger une pièce jointe
     */
    public function downloadAttachment(MessageAttachment $attachment)
    {
        // Vérifier que l'utilisateur est destinataire ou expéditeur
        $user = Auth::user();
        $message = $attachment->message;
        
        $isAuthorized = $message->sender_id === $user->id || 
                        $message->recipients()->where('user_id', $user->id)->exists();
        
        if (!$isAuthorized) {
            abort(403, 'Non autorisé');
        }

        $path = storage_path('app/' . $attachment->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'Fichier non trouvé');
        }

        return response()->download($path, $attachment->original_filename);
    }

    /**
     * Marquer une conversation comme lue
     */
    private function markConversationAsRead(Conversation $conversation, int $userId): void
    {
        MessageRecipient::whereIn('message_id', function ($query) use ($conversation, $userId) {
                $query->select('id')
                    ->from('messages')
                    ->where('conversation_id', $conversation->id)
                    ->where('sender_id', '!=', $userId);
            })
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Récupérer les nouveaux messages (polling temps réel)
     */
    public function poll(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'last_message_id' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $conversation = Conversation::findOrFail($validated['conversation_id']);

        // Vérifier l'accès
        if (!$conversation->members->contains($user->id)) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Récupérer les nouveaux messages
        $query = $conversation->messages()
            ->with(['sender', 'attachments'])
            ->where('sender_id', '!=', $user->id);

        if ($validated['last_message_id']) {
            $query->where('id', '>', $validated['last_message_id']);
        }

        $messages = $query->orderBy('created_at', 'asc')->get();

        // Déchiffrer et formater
        $messages->transform(function ($message) {
            $message->decrypted_body = $message->decrypt();
            $message->time_formatted = $message->created_at->format('H:i');
            return $message;
        });

        // Marquer comme lus
        $this->markConversationAsRead($conversation, $user->id);

        return response()->json([
            'messages' => $messages,
            'count' => $messages->count(),
        ]);
    }

    /**
     * Compteur de messages non lus
     */
    public function unreadCount()
    {
        $count = MessageRecipient::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Supprimer un message
     */
    public function destroy(Message $message)
    {
        $user = Auth::user();
        
        // Seul l'expéditeur peut supprimer son message
        if ($message->sender_id !== $user->id) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Supprimer les pièces jointes
        foreach ($message->attachments as $attachment) {
            Storage::disk('local')->delete($attachment->file_path);
            $attachment->delete();
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Rechercher dans les messages
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $user = Auth::user();
        $searchTerm = strtolower($validated['q']);

        // Récupérer les messages des conversations de l'utilisateur
        $messages = Message::whereHas('conversation.members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->where('sender_id', '!=', $user->id)
            ->with(['sender', 'conversation'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->filter(function ($message) use ($searchTerm) {
                $decrypted = strtolower($message->decrypt() ?? '');
                return str_contains($decrypted, $searchTerm);
            })
            ->values();

        return response()->json([
            'results' => $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'conversation_id' => $message->conversation_id,
                    'sender' => $message->sender->name,
                    'content' => Str::limit($message->decrypt(), 100),
                    'date' => $message->created_at->format('d/m/Y H:i'),
                ];
            })
        ]);
    }
}