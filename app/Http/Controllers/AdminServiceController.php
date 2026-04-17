<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use App\Models\Employe;
use App\Models\WifiZone;
use App\Models\Routeur;
use App\Models\Statistique;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin_service|super_admin']);
    }

    public function dashboard()
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            // Consommation 7 jours pour graphique (données vides)
            $conso7Jours = collect();
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $conso7Jours->push((object) [
                    'date' => $date,
                    'download' => 0,
                    'upload' => 0,
                ]);
            }
            
            return view('dashboards.admin-service', [
                'service' => null,
                'error' => 'Aucun service assigné. Contactez votre administrateur.',
                'statsService' => ['total_employes' => 0, 'actifs' => 0, 'inactifs' => 0],
                'employes' => collect(),
                'consommationService' => collect(),
                'zonesWifi' => 0,
                'employesConnectes' => 0,
                'employesConso' => collect(),
                'appareilsConnectes' => collect(),
                'conso7Jours' => $conso7Jours,
                'quotaTotal' => 0,
                'dataUsed' => 0,
                'quotaPourcent' => 0
            ]);
        }

        $employes = User::where('service_id', $service->id)
            ->with('roles')
            ->paginate(10);

        $statsService = [
            'total_employes' => User::where('service_id', $service->id)->count(),
            'actifs' => User::where('service_id', $service->id)->where('est_actif', true)->count(),
            'inactifs' => User::where('service_id', $service->id)->where('est_actif', false)->count(),
        ];

        // Consommation du service (tous les employés du service)
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        $consommationService = Statistique::whereDate('timestamp', '>=', now()->subDays(7))
            ->selectRaw('DATE(timestamp) as date, SUM(valeur) as total, unite')
            ->groupByRaw('DATE(timestamp), unite')
            ->orderBy('date')
            ->get();

        // Nouvelles données enrichies
        $zonesWifi = \App\Models\WifiZone::where('active', true)->count();
        $employesConnectes = \App\Models\Employe::whereIn('user_id', $employeIds)
            ->where('active', true)
            ->whereNotNull('last_connected_at')
            ->where('last_connected_at', '>=', now()->subHours(1))
            ->count();

        // Quota mensuel du service
        $employesReseau = \App\Models\Employe::whereIn('user_id', $employeIds)->get();
        $quotaTotal = $employesReseau->sum('quota_monthly');
        $dataUsed = $employesReseau->sum('data_used_this_month');
        $quotaPourcent = $quotaTotal > 0 ? round(($dataUsed / $quotaTotal) * 100, 1) : 0;

        // Employés avec leur consommation individuelle
        $employesConso = \App\Models\Employe::whereIn('user_id', $employeIds)
            ->with(['user', 'wifiZone'])
            ->orderByDesc('data_used_this_month')
            ->get()
            ->map(function ($emp) {
                $emp->quota_pourcent = $emp->quota_monthly > 0 
                    ? round(($emp->data_used_this_month / $emp->quota_monthly) * 100, 1) 
                    : 0;
                return $emp;
            });

        // Appareils connectés maintenant
        $appareilsConnectes = \App\Models\Employe::whereIn('user_id', $employeIds)
            ->where('active', true)
            ->whereNotNull('last_connected_at')
            ->where('last_connected_at', '>=', now()->subHours(1))
            ->with(['user', 'wifiZone'])
            ->get();

        // Consommation 7 jours pour graphique
        $conso7Jours = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $conso7Jours->push((object) [
                'date' => $date,
                'download' => rand(200, 800),
                'upload' => rand(100, 400),
            ]);
        }

        return view('dashboards.admin-service', compact(
            'service', 'employes', 'statsService', 'consommationService',
            'zonesWifi', 'employesConnectes', 'quotaTotal', 'dataUsed', 'quotaPourcent',
            'employesConso', 'appareilsConnectes', 'conso7Jours'
        ));
    }

    public function employesIndex()
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service assigné.');
        }

        $employes = User::where('service_id', $service->id)
            ->with('roles')
            ->paginate(10);

        return view('admin-service.employes', compact('employes', 'service'));
    }

    public function employesCreate()
    {
        $user = Auth::user();
        $service = $user->service;
        $roles = Role::whereIn('name', ['employe', 'admin_service'])->pluck('name', 'name');

        return view('admin-service.employes-create', compact('service', 'roles'));
    }

    public function employesStore(Request $request)
    {
        $user = Auth::user();
        $service = $user->service;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:50',
            'fonction' => 'nullable|string|max:100',
            'role' => 'required|in:employe,admin_service',
        ]);

        $newUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'telephone' => $data['telephone'] ?? null,
            'fonction' => $data['fonction'] ?? null,
            'service_id' => $service->id,
            'est_actif' => true,
        ]);

        $newUser->assignRole($data['role']);

        return redirect()->route('admin-service.employes')
            ->with('success', 'Employé créé avec succès.');
    }

    public function employesEdit(User $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        // Vérifier que l'employé appartient au même service
        if ($employe->service_id !== $service->id && !$user->isSuperAdmin()) {
            abort(403, 'Cet employé n\'appartient pas à votre service.');
        }

        $roles = Role::whereIn('name', ['employe', 'admin_service'])->pluck('name', 'name');

        return view('admin-service.employes-edit', compact('employe', 'service', 'roles'));
    }

    public function employesUpdate(Request $request, User $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if ($employe->service_id !== $service->id && !$user->isSuperAdmin()) {
            abort(403, 'Cet employé n\'appartient pas à votre service.');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employe->id,
            'password' => 'nullable|string|min:8|confirmed',
            'telephone' => 'nullable|string|max:50',
            'fonction' => 'nullable|string|max:100',
            'est_actif' => 'nullable|boolean',
            'role' => 'required|in:employe,admin_service',
        ]);

        $employe->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'telephone' => $data['telephone'] ?? null,
            'fonction' => $data['fonction'] ?? null,
            'est_actif' => $request->filled('est_actif'),
        ]);

        if (!empty($data['password'])) {
            $employe->password = Hash::make($data['password']);
            $employe->save();
        }

        $employe->syncRoles([$data['role']]);

        return redirect()->route('admin-service.employes')
            ->with('success', 'Employé mis à jour.');
    }

    public function employesDestroy(User $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if ($employe->service_id !== $service->id && !$user->isSuperAdmin()) {
            abort(403, 'Cet employé n\'appartient pas à votre service.');
        }

        if (Auth::id() === $employe->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $employe->delete();

        return redirect()->route('admin-service.employes')
            ->with('success', 'Employé supprimé.');
    }

    public function statsConsommation()
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return view('admin-service.stats', [
                'service' => null,
                'stats' => collect()
            ]);
        }

        $employesIds = User::where('service_id', $service->id)->pluck('id');

        $stats = Statistique::whereDate('timestamp', '>=', now()->subDays(30))
            ->selectRaw('DATE(timestamp) as date, type, SUM(valeur) as total, unite')
            ->groupByRaw('DATE(timestamp), type, unite')
            ->orderBy('date')
            ->get();

        return view('admin-service.stats', compact('service', 'stats'));
    }

    // ==================== EMPLOYES RESEAU (WiFi) ====================

    /**
     * Liste des employés réseau du service
     */
    public function employesReseauIndex()
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service assigné.');
        }

        // Récupérer les employés réseau du service via le user_id
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        $employesReseau = Employe::whereIn('user_id', $employeIds)
            ->with(['user', 'wifiZone'])
            ->orderByDesc('last_connected_at')
            ->get();

        // Récupérer les zones WiFi des routeurs du service
        $routeurs = Routeur::where('service_id', $service->id)->pluck('id');
        $zonesWifi = WifiZone::whereIn('routeur_id', $routeurs)
            ->where('active', true)
            ->get();

        return view('admin-service.employes-reseau', compact('employesReseau', 'zonesWifi', 'service'));
    }

    /**
     * Créer un nouvel employé réseau
     */
    public function employesReseauStore(Request $request)
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service assigné.');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:employes,email',
            'telephone' => 'nullable|string|max:20',
            'matricule' => 'nullable|string|max:50|unique:employes,matricule',
            'departement' => 'nullable|string|max:100',
            'poste' => 'nullable|string|max:100',
            'wifi_zone_id' => 'required|exists:wifi_zones,id',
            'mac_address' => 'nullable|string|max:17',
            'bandwidth_down' => 'nullable|integer|min:0',
            'bandwidth_up' => 'nullable|integer|min:0',
            'quota_monthly' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Récupérer la zone WiFi pour obtenir le routeur_id
        $zone = WifiZone::find($validated['wifi_zone_id']);

        // Créer l'employé réseau lié à l'utilisateur courant (admin service)
        $employe = Employe::create([
            'routeur_id' => $zone->routeur_id,
            'wifi_zone_id' => $validated['wifi_zone_id'],
            'user_id' => $user->id,
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
            'data_used_this_month' => 0,
            'data_used_total' => 0,
            'notes' => $validated['notes'] ?? null,
            'active' => true,
        ]);

        return redirect()->route('admin-service.employes-reseau.index')
            ->with('success', 'Employé réseau "' . $employe->fullName() . '" créé avec succès.');
    }

    /**
     * Modifier un employé réseau
     */
    public function employesReseauEdit(Employe $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service assigné.');
        }

        // Vérifier que l'employé appartient au service
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        if (!$employeIds->contains($employe->user_id)) {
            abort(403, 'Cet employé n\'appartient pas à votre service.');
        }

        $routeurs = Routeur::where('service_id', $service->id)->pluck('id');
        $zonesWifi = WifiZone::whereIn('routeur_id', $routeurs)
            ->where('active', true)
            ->get();

        return view('admin-service.employes-reseau-edit', compact('employe', 'zonesWifi', 'service'));
    }

    /**
     * Mettre à jour un employé réseau
     */
    public function employesReseauUpdate(Request $request, Employe $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service assigné.');
        }

        // Vérifier que l'employé appartient au service
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        if (!$employeIds->contains($employe->user_id)) {
            abort(403, 'Cet employé n\'appartient pas à votre service.');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:employes,email,' . $employe->id,
            'telephone' => 'nullable|string|max:20',
            'matricule' => 'nullable|string|max:50|unique:employes,matricule,' . $employe->id,
            'departement' => 'nullable|string|max:100',
            'poste' => 'nullable|string|max:100',
            'wifi_zone_id' => 'required|exists:wifi_zones,id',
            'mac_address' => 'nullable|string|max:17',
            'bandwidth_down' => 'nullable|integer|min:0',
            'bandwidth_up' => 'nullable|integer|min:0',
            'quota_monthly' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $zone = WifiZone::find($validated['wifi_zone_id']);

        $employe->update([
            'routeur_id' => $zone->routeur_id,
            'wifi_zone_id' => $validated['wifi_zone_id'],
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
        ]);

        return redirect()->route('admin-service.employes-reseau.index')
            ->with('success', 'Employé réseau "' . $employe->fullName() . '" mis à jour.');
    }

    /**
     * Bloquer/Débloquer un employé réseau
     */
    public function employesReseauToggle(Employe $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service assigné.');
        }

        // Vérifier que l'employé appartient au service
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        if (!$employeIds->contains($employe->user_id)) {
            abort(403, 'Cet employé n\'appartient pas à votre service.');
        }

        $employe->active = !$employe->active;
        $employe->save();

        $status = $employe->active ? 'débloqué' : 'bloqué';
        return redirect()->route('admin-service.employes-reseau.index')
            ->with('success', 'Employé "' . $employe->fullName() . '" ' . $status . '.');
    }

    /**
     * Supprimer un employé réseau
     */
    public function employesReseauDestroy(Employe $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return redirect()->route('dashboard')->with('error', 'Aucun service assigné.');
        }

        // Vérifier que l'employé appartient au service
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        if (!$employeIds->contains($employe->user_id)) {
            abort(403, 'Cet employé n\'appartient pas à votre service.');
        }

        $nom = $employe->fullName();
        $employe->delete();

        return redirect()->route('admin-service.employes-reseau.index')
            ->with('success', 'Employé réseau "' . $nom . '" supprimé.');
    }

    /**
     * Stats temps réel d'un employé (AJAX)
     */
    public function employesReseauRealtime(Employe $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return response()->json(['error' => 'Aucun service assigné.'], 403);
        }

        // Vérifier que l'employé appartient au service
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        if (!$employeIds->contains($employe->user_id)) {
            return response()->json(['error' => 'Accès interdit.'], 403);
        }

        // Simuler des données temps réel (à remplacer par des données MikroTik réelles)
        return response()->json([
            'employe' => $employe->fullName(),
            'download' => rand(10, 100) + rand(0, 99) / 100,
            'upload' => rand(5, 50) + rand(0, 99) / 100,
            'ping' => rand(10, 100),
            'connected_since' => $employe->last_connected_at?->diffForHumans(),
        ]);
    }

    /**
     * Historique des connexions d'un employé (AJAX)
     */
    public function employesReseauHistory(Employe $employe)
    {
        $user = Auth::user();
        $service = $user->service;

        if (!$service) {
            return response()->json(['error' => 'Aucun service assigné.'], 403);
        }

        // Vérifier que l'employé appartient au service
        $employeIds = User::where('service_id', $service->id)->pluck('id');
        if (!$employeIds->contains($employe->user_id)) {
            return response()->json(['error' => 'Accès interdit.'], 403);
        }

        // Récupérer l'historique depuis les statistiques
        $history = Statistique::where('user_id', $employe->user_id)
            ->whereDate('timestamp', '>=', now()->subDays(30))
            ->orderByDesc('timestamp')
            ->limit(50)
            ->get()
            ->map(function ($stat) {
                return [
                    'date' => $stat->timestamp->format('Y-m-d H:i'),
                    'type' => $stat->type,
                    'valeur' => $stat->valeur,
                    'unite' => $stat->unite,
                ];
            });

        return response()->json([
            'employe' => $employe->fullName(),
            'history' => $history,
        ]);
    }
}
