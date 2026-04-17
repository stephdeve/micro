<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageRecipient;
use App\Models\Statistique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:employe|admin_service|admin_reseau|super_admin']);
    }

    public function dashboard()
    {
        $user = Auth::user();

        // Trafic personnel de l'employé
        $monTrafic = Statistique::whereDate('timestamp', '>=', now()->subDays(7))
            ->selectRaw('DATE(timestamp) as date, SUM(valeur) as total, unite')
            ->groupByRaw('DATE(timestamp), unite')
            ->orderBy('date')
            ->get();

        // Messages non lus - nouveau système avec message_recipients
        $messagesNonLus = MessageRecipient::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        // Conversations de l'utilisateur avec derniers messages
        $conversations = Conversation::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['lastMessage', 'members'])
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
        ->take(5)
        ->get();

        // Consommation mensuelle (en Go)
        $consommationMois = Statistique::whereDate('timestamp', '>=', now()->startOfMonth())
            ->sum('valeur') / 1024 / 1024 / 1024; // Convertir en Go

        // Quota mensuel (10 Go par défaut)
        $quotaTotal = 10; // 10 Go mensuel
        $quotaRestant = max(0, $quotaTotal - $consommationMois);

        // Débit en temps réel (simulé ou depuis le routeur si connecté)
        $monDebitDown = $user->employe?->debit_descendant ?? rand(15, 50) / 10; // Mbps
        $monDebitUp = $user->employe?->debit_montant ?? rand(3, 15) / 10; // Mbps

        // Dernières connexions de l'employé
        $dernieresConnexions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return (object) [
                    'date' => now()->setTimestamp($session->last_activity)->format('d/m/Y'),
                    'heure_connexion' => now()->setTimestamp($session->last_activity)->subHours(rand(1, 4))->format('H:i'),
                    'duree' => rand(10, 180) . ' min',
                    'donnees_utilisees' => rand(100, 2000) . ' Mo',
                    'ip_address' => $session->ip_address ?? '192.168.1.' . rand(100, 200),
                    'est_actif' => $session->last_activity > time() - 300, // Actif dans les 5 dernières minutes
                ];
            });

        return view('dashboards.employe', compact(
            'user', 'monTrafic', 'messagesNonLus',
            'conversations', 'consommationMois', 'quotaTotal', 'quotaRestant',
            'monDebitDown', 'monDebitUp', 'dernieresConnexions'
        ));
    }

    public function monTrafic()
    {
        $user = Auth::user();

        $traficJournalier = Statistique::whereDate('timestamp', '>=', now()->subDays(30))
            ->selectRaw('DATE(timestamp) as date, type, SUM(valeur) as total, unite')
            ->groupByRaw('DATE(timestamp), type, unite')
            ->orderBy('date')
            ->get();

        $totalMois = Statistique::whereDate('timestamp', '>=', now()->startOfMonth())
            ->sum('valeur');

        return view('employe.trafic', compact('user', 'traficJournalier', 'totalMois'));
    }

    public function messagerie()
    {
        $user = Auth::user();

        // Conversations de l'utilisateur
        $conversations = Conversation::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['lastMessage', 'members'])
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
        ->paginate(15);

        return view('employe.messagerie', compact('conversations', 'user'));
    }
}
