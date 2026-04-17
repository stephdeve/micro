<?php

namespace App\Http\Controllers;

use App\Models\Employe;
use App\Models\Routeur;
use App\Models\WifiZone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeNetworkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Liste des employés d'un routeur
     */
    public function index(Routeur $routeur)
    {
        $employes = $routeur->employes()
            ->with('wifiZone')
            ->orderBy('nom')
            ->paginate(20);

        $wifiZones = $routeur->wifiZones()->where('active', true)->get();

        // Stats
        $stats = [
            'total' => $routeur->employes()->count(),
            'online' => $routeur->employes()
                ->where('active', true)
                ->where('last_connected_at', '>=', now()->subMinutes(5))
                ->count(),
            'blocked' => $routeur->employes()->where('active', false)->count(),
        ];

        return view('employes.index', compact('routeur', 'employes', 'wifiZones', 'stats'));
    }

    /**
     * Créer un nouvel employé
     */
    public function store(Request $request, Routeur $routeur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:employes,email',
            'telephone' => 'nullable|string|max:20',
            'matricule' => 'nullable|string|max:50|unique:employes,matricule',
            'departement' => 'nullable|string|max:100',
            'poste' => 'nullable|string|max:100',
            'wifi_zone_id' => 'nullable|exists:wifi_zones,id',
            'mac_address' => 'nullable|string|max:17',
            'bandwidth_down' => 'nullable|integer|min:0',
            'bandwidth_up' => 'nullable|integer|min:0',
            'quota_monthly' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'create_user_account' => 'boolean',
        ]);

        // Créer le compte utilisateur si demandé
        $userId = null;
        if ($request->boolean('create_user_account')) {
            $password = Str::random(10);
            $user = User::create([
                'name' => $validated['prenom'] . ' ' . $validated['nom'],
                'email' => $validated['email'],
                'password' => Hash::make($password),
                'role' => 'employe',
            ]);
            $userId = $user->id;

            // TODO: Envoyer email avec le mot de passe temporaire
        }

        // Créer l'employé
        $employe = Employe::create([
            'routeur_id' => $routeur->id,
            'wifi_zone_id' => $validated['wifi_zone_id'] ?? null,
            'user_id' => $userId,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'matricule' => $validated['matricule'] ?? null,
            'departement' => $validated['departement'] ?? null,
            'poste' => $validated['poste'] ?? null,
            'mac_address' => $validated['mac_address'] ?? null,
            'bandwidth_down' => $validated['bandwidth_down'] ?? 0,
            'bandwidth_up' => $validated['bandwidth_up'] ?? 0,
            'quota_monthly' => $validated['quota_monthly'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'active' => true,
        ]);

        return redirect()->route('routeurs.employes.index', $routeur)
            ->with('success', 'Employé "' . $employe->fullName() . '" créé avec succès' . ($userId ? ' avec un compte utilisateur' : ''));
    }

    /**
     * Mettre à jour un employé
     */
    public function update(Request $request, Routeur $routeur, Employe $employe)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:employes,email,' . $employe->id,
            'telephone' => 'nullable|string|max:20',
            'matricule' => 'nullable|string|max:50|unique:employes,matricule,' . $employe->id,
            'departement' => 'nullable|string|max:100',
            'poste' => 'nullable|string|max:100',
            'wifi_zone_id' => 'nullable|exists:wifi_zones,id',
            'mac_address' => 'nullable|string|max:17',
            'bandwidth_down' => 'nullable|integer|min:0',
            'bandwidth_up' => 'nullable|integer|min:0',
            'quota_monthly' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $employe->update([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'matricule' => $validated['matricule'] ?? null,
            'departement' => $validated['departement'] ?? null,
            'poste' => $validated['poste'] ?? null,
            'wifi_zone_id' => $validated['wifi_zone_id'] ?? null,
            'mac_address' => $validated['mac_address'] ?? null,
            'bandwidth_down' => $validated['bandwidth_down'] ?? 0,
            'bandwidth_up' => $validated['bandwidth_up'] ?? 0,
            'quota_monthly' => $validated['quota_monthly'] ?? 0,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Mettre à jour le compte utilisateur associé si existe
        if ($employe->user) {
            $employe->user->update([
                'name' => $validated['prenom'] . ' ' . $validated['nom'],
                'email' => $validated['email'],
            ]);
        }

        return redirect()->route('routeurs.employes.index', $routeur)
            ->with('success', 'Employé "' . $employe->fullName() . '" mis à jour');
    }

    /**
     * Bloquer/Débloquer un employé
     */
    public function toggle(Routeur $routeur, Employe $employe)
    {
        $employe->active = !$employe->active;
        $employe->save();

        // Bloquer/Débloquer aussi le compte utilisateur
        if ($employe->user) {
            $employe->user->update(['active' => $employe->active]);
        }

        $status = $employe->active ? 'débloqué' : 'bloqué';
        return redirect()->route('routeurs.employes.index', $routeur)
            ->with('success', 'Employé "' . $employe->fullName() . '" ' . $status);
    }

    /**
     * Supprimer un employé
     */
    public function destroy(Routeur $routeur, Employe $employe)
    {
        $nom = $employe->fullName();

        // Supprimer le compte utilisateur associé
        if ($employe->user) {
            $employe->user->delete();
        }

        $employe->delete();

        return redirect()->route('routeurs.employes.index', $routeur)
            ->with('success', 'Employé "' . $nom . '" supprimé');
    }

    /**
     * Voir les détails et historique d'un employé
     */
    public function show(Routeur $routeur, Employe $employe)
    {
        // Charger les relations
        $employe->load('wifiZone', 'user');

        // Historique des connexions (simulé pour l'instant)
        $historique = [];

        // Statistiques
        $stats = [
            'total_data' => $employe->dataUsedFormatted(),
            'connections_count' => rand(10, 50), // TODO: implémenter vraiment
            'avg_duration' => rand(30, 120), // minutes
            'quota_percent' => $employe->quotaUsedPercent(),
        ];

        return view('employes.show', compact('routeur', 'employe', 'historique', 'stats'));
    }

    /**
     * Obtenir les détails pour édition (AJAX)
     */
    public function edit(Routeur $routeur, Employe $employe)
    {
        return response()->json([
            'employe' => $employe,
            'wifi_zone' => $employe->wifiZone,
        ]);
    }

    /**
     * Consommation en temps réel (AJAX)
     */
    public function realtimeStats(Routeur $routeur, Employe $employe)
    {
        // TODO: Récupérer vraiment les stats temps réel du routeur
        // Pour l'instant, simulons
        $currentSpeed = [
            'down' => rand(100, 5000), // kbps
            'up' => rand(50, 2000), // kbps
        ];

        return response()->json([
            'speed' => $currentSpeed,
            'data_used' => $employe->data_used_this_month,
            'data_total' => $employe->quota_monthly,
            'data_percent' => $employe->quotaUsedPercent(),
            'is_online' => $employe->last_connected_at && $employe->last_connected_at->gt(now()->subMinutes(5)),
        ]);
    }

    /**
     * Dashboard employé - Vue de l'employé connecté
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Vérifier si l'utilisateur est lié à un employé
        $employe = Employe::where('user_id', $user->id)->first();

        if (!$employe) {
            return view('employes.no-profile', compact('user'));
        }

        // Charger les relations
        $employe->load('wifiZone', 'routeur');

        // Messages non lus (si vous avez un système de messagerie)
        $unreadMessages = 0; // TODO: implémenter

        // Historique récent
        $recentConnections = []; // TODO: implémenter

        return view('employes.dashboard', compact('employe', 'unreadMessages', 'recentConnections'));
    }
}
