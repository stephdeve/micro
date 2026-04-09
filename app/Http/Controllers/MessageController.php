<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $folder = $request->get('folder', 'inbox');
        $query = Message::query();

        // Filtrer par dossier
        switch($folder) {
            case 'inbox':
                $query->where('receiver_id', Auth::id())->where('folder', 'inbox');
                break;
            case 'sent':
                $query->where('sender_id', Auth::id())->where('folder', 'sent');
                break;
            case 'starred':
                $query->where('receiver_id', Auth::id())->where('is_starred', true);
                break;
            case 'archive':
                $query->where('folder', 'archive')
                      ->where(function($q) {
                          $q->where('receiver_id', Auth::id())
                            ->orWhere('sender_id', Auth::id());
                      });
                break;
            case 'trash':
                $query->where('receiver_id', Auth::id())->where('folder', 'trash');
                break;
        }

        // Filtres supplémentaires
        if ($request->has('unread')) {
            $query->where('is_read', false);
        }

        if ($request->has('attachments')) {
            $query->where('has_attachments', true);
        }

        if ($request->has('starred')) {
            $query->where('is_starred', true);
        }

        if ($request->has('urgent')) {
            $query->whereIn('priority', ['haute', 'urgente']);
        }

        if ($request->filled('tag')) {
            $query->where('tag', $request->tag);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $messages = $query->with('sender')
                         ->latest()
                         ->paginate(15);

        // Statistiques
        $stats = [
            'total' => Message::where('receiver_id', Auth::id())->count(),
            'non_lus' => Message::where('receiver_id', Auth::id())->where('is_read', false)->count(),
            'importants' => Message::where('receiver_id', Auth::id())->whereIn('priority', ['haute', 'urgente'])->count(),
            'utilisateurs' => User::count(),
            'chiffrement' => 'TLS 1.3',
            'algorithme' => 'AES-256-GCM'
        ];

        $users = User::where('id', '!=', Auth::id())->get();

        return view('messagerie', compact('messages', 'stats', 'folder', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required_if:send_to_all,0|nullable|exists:users,id',
            'send_to_all' => 'nullable|boolean',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'in:basse,normale,haute,urgente',
            'tag' => 'nullable|string|max:50',
            'attachments.*' => 'nullable|file|max:10240', // 10MB max
        ]);

        $sendToAll = $request->boolean('send_to_all');
        $receivers = $sendToAll
            ? User::where('id', '!=', Auth::id())->get()
            : User::where('id', $request->receiver_id)->get();

        // Message envoyé (archive envoyé)
        $encryptedContent = $request->is_secure ? Crypt::encryptString($request->content) : $request->content;

        $sentMessage = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $sendToAll ? null : $request->receiver_id,
            'subject' => $request->subject,
            'content' => $encryptedContent,
            'priority' => $request->priority ?? 'normale',
            'is_secure' => $request->has('is_secure'),
            'folder' => 'sent',
            'tag' => $request->tag ?? 'reseau',
        ]);

        $attachmentParams = [];
        // Gérer les pièces jointes pour le message envoyé et en copie
        if ($request->hasFile('attachments')) {
            $sentMessage->has_attachments = true;
            $sentMessage->save();

            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments/' . $sentMessage->id, 'public');
                $attachmentParams[] = [
                    'filename' => $file->getClientOriginalName(),
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getMimeType(),
                ];
            }

            foreach ($attachmentParams as $params) {
                $sentMessage->attachments()->create($params);
            }
        }

        // Créer le message pour chaque destinataire (inbox)
        foreach ($receivers as $receiver) {
            $receiverMessage = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiver->id,
                'subject' => $request->subject,
                'content' => $encryptedContent,
                'priority' => $request->priority ?? 'normale',
                'is_secure' => $request->has('is_secure'),
                'folder' => 'inbox',
                'tag' => $request->tag ?? 'reseau',
            ]);

            if (!empty($attachmentParams)) {
                $receiverMessage->has_attachments = true;
                $receiverMessage->save();
                foreach ($attachmentParams as $params) {
                    $receiverMessage->attachments()->create($params);
                }
            }

            // Notification pour chaque destinataire
            try {
                $receiver->notify(new MessageReceivedNotification(
                    Auth::user()->name,
                    $request->subject,
                    'Vous avez reçu un message de ' . Auth::user()->name . ' : ' . $request->subject,
                    route('messagerie.show', $receiverMessage),
                    $receiverMessage->id
                ));
            } catch (\Exception $e) {
                Log::error('Notification email message failed', [
                    'receiver_id' => $receiver->id,
                    'message_id' => $receiverMessage->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $sentMessage]);
        }

        return redirect()->route('messagerie.index')->with('success', 'Message envoyé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $messagerie)
    {
        // Marquer comme lu si c'est le destinataire
        if ($messagerie->receiver_id == Auth::id() && !$messagerie->is_read) {
            $messagerie->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        $messagerie->load('sender', 'receiver', 'attachments');

        // Déchiffrer si nécessaire
        if ($messagerie->is_secure) {
            try {
                $messagerie->content = Crypt::decryptString($messagerie->content);
            } catch (\Exception $e) {
                $messagerie->content = '[Impossible de déchiffrer ce message]';
            }
        }

        return view('messages.show', ['message' => $messagerie]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $messagerie)
    {
        // Pour marquer comme lu/non lu, favori, etc.
        if ($request->has('is_starred')) {
            $messagerie->update(['is_starred' => !$messagerie->is_starred]);
            return response()->json(['success' => true, 'is_starred' => $messagerie->is_starred]);
        }

        if ($request->has('is_read')) {
            $messagerie->update(['is_read' => !$messagerie->is_read]);
            return response()->json(['success' => true, 'is_read' => $messagerie->is_read]);
        }

        return response()->json(['success' => false], 400);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $messagerie)
    {
        // Soft delete ou déplacer vers la corbeille
        if ($messagerie->folder == 'trash') {
            $messagerie->delete(); // Suppression définitive
        } else {
            $messagerie->update(['folder' => 'trash']);
        }

        return redirect()->route('messagerie.index')->with('success', 'Message déplacé vers la corbeille.');
    }

    /**
     * Restaurer un message depuis la corbeille.
     */
    public function restore($id)
    {
        $message = Message::withTrashed()->findOrFail($id);
        
        // Vérifier que le message appartient à l'utilisateur
        if ($message->receiver_id !== Auth::id() && $message->sender_id !== Auth::id()) {
            abort(403, 'Non autorisé');
        }
        
        $message->folder = 'inbox';
        $message->save();

        return redirect()->route('messagerie.index', ['folder' => 'inbox'])->with('success', 'Message restauré.');
    }

    /**
     * Archiver un message.
     */
    public function archive(Message $messagerie)
    {
        // Vérifier que le message appartient à l'utilisateur (en tant que destinataire ou expéditeur)
        if ($messagerie->receiver_id !== Auth::id() && $messagerie->sender_id !== Auth::id()) {
            abort(403, 'Non autorisé');
        }

        $messagerie->update(['folder' => 'archive']);
        return redirect()->route('messagerie.index', ['folder' => 'archive'])->with('success', 'Message archivé.');
    }

    /**
     * Basculer étoile (favori) d'un message.
     */
    public function toggleStar(Message $messagerie)
    {
        // Vérifier que le message appartient à l'utilisateur
        if ($messagerie->receiver_id !== Auth::id() && $messagerie->sender_id !== Auth::id()) {
            abort(403, 'Non autorisé');
        }

        $messagerie->update(['is_starred' => !$messagerie->is_starred]);
        return response()->json(['success' => true, 'is_starred' => $messagerie->is_starred]);
    }

    /**
     * Télécharger une pièce jointe.
     */
    public function downloadAttachment($messagerie, $attachment)
    {
        $message = Message::findOrFail($messagerie);
        $file = $message->attachments()->findOrFail($attachment);
        
        return Storage::disk('public')->download($file->file_path, $file->filename);
    }

    /**
     * Effectuer une action en batch sur plusieurs messages.
     */
    public function batchAction(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array|min:1',
            'message_ids.*' => 'exists:messages,id',
            'action' => 'required|in:trash,delete',
        ]);

        $messageIds = $request->message_ids;
        $action = $request->action;

        if ($action === 'trash') {
            // Déplacer vers la corbeille
            Message::whereIn('id', $messageIds)
                ->where(function($q) {
                    $q->where('receiver_id', Auth::id())
                      ->orWhere('sender_id', Auth::id());
                })
                ->update(['folder' => 'trash']);
        } elseif ($action === 'delete') {
            // Supprimer définitivement uniquement les messages de la corbeille
            Message::whereIn('id', $messageIds)
                ->where('folder', 'trash')
                ->where(function($q) {
                    $q->where('receiver_id', Auth::id())
                      ->orWhere('sender_id', Auth::id());
                })
                ->delete();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Action effectuée avec succès.']);
        }

        return redirect()->back()->with('success', 'Action effectuée avec succès.');
    }
}