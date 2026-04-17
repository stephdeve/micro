<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use App\Models\Routeur;
use App\Models\Securite;
use App\Models\Statistique;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    public function dashboard()
    {
        // Vue globale de toute l'infrastructure
        $totalUsers = User::count();
        $totalServices = Service::count();
        $totalRouteurs = Routeur::count();
        $routeursEnLigne = Routeur::where('statut', 'en_ligne')->count();
        $routeursHorsLigne = Routeur::where('statut', 'hors_ligne')->count();

        $alertesActives = Securite::where('statut', 'nouveau')->count();
        $incidentCritiques = Securite::where('severite', 'critique')
            ->whereDate('created_at', '>=', now()->subDay())
            ->count();

        // Services avec nombre d'employés
        $services = Service::withCount('employes')->with('responsable')->get();

        // Répartition des rôles
        $rolesStats = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('COUNT(*) as count'))
            ->groupBy('roles.name')
            ->pluck('count', 'name')
            ->toArray();

        // Performance globale
        $globalPerf = [
            'cpu' => round(Routeur::whereNotNull('cpu_usage')->avg('cpu_usage') ?? 0, 1),
            'memory' => round(Routeur::whereNotNull('memory_usage')->avg('memory_usage') ?? 0, 1),
            'temperature' => round(Routeur::whereNotNull('temperature')->avg('temperature') ?? 0, 1),
        ];

        // Bande passante totale
        $bandePassante = \App\Models\InterfaceModel::sum('debit_entrant') + \App\Models\InterfaceModel::sum('debit_sortant');

        // Statistiques de trafic (7 derniers jours)
        $traficSemaine = Statistique::whereDate('timestamp', '>=', now()->subDays(7))
            ->selectRaw('DATE(timestamp) as date, SUM(valeur) as total')
            ->groupByRaw('DATE(timestamp)')
            ->orderBy('date')
            ->get();

        // Dernières alertes
        $dernieresAlertes = Securite::where('type', 'alerte')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboards.super-admin', compact(
            'totalUsers', 'totalServices', 'totalRouteurs',
            'routeursEnLigne', 'routeursHorsLigne',
            'alertesActives', 'incidentCritiques',
            'services', 'rolesStats', 'globalPerf',
            'bandePassante', 'traficSemaine', 'dernieresAlertes'
        ));
    }
}
