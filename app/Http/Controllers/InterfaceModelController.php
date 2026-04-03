<?php

namespace App\Http\Controllers;

use App\Models\InterfaceModel;
use App\Models\Routeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterfaceModelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = InterfaceModel::with('routeur');

        // Filtres
        if ($request->filled('routeur_id')) {
            $query->where('routeur_id', $request->routeur_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('adresse_ip', 'like', '%' . $request->search . '%')
                  ->orWhere('adresse_mac', 'like', '%' . $request->search . '%');
            });
        }

        $interfaces = $query->latest()->paginate(5);
        
        // Récupérer tous les routeurs pour les filtres
        $routeurs = Routeur::orderBy('nom')->get();
        
        // Statistiques
        $stats = [
            'totales' => InterfaceModel::count(),
            'actives' => InterfaceModel::where('statut', 'actif')->count(),
            'routeurs' => Routeur::count(),
            'nouvelles' => InterfaceModel::whereDate('created_at', today())->count(),
            'debit_total' => InterfaceModel::sum('debit_entrant') + InterfaceModel::sum('debit_sortant'),
            'debit_entrant' => InterfaceModel::sum('debit_entrant'),
            'debit_sortant' => InterfaceModel::sum('debit_sortant'),
            'erreurs' => InterfaceModel::sum('rx_errors') + InterfaceModel::sum('tx_errors'),
        ];

        // Top interfaces par trafic
        $topInterfaces = InterfaceModel::where('statut', 'actif')
            ->orderByRaw('(debit_entrant + debit_sortant) DESC')
            ->take(5)
            ->get()
            ->map(function($interface) {
                $total = $interface->debit_entrant + $interface->debit_sortant;
                $maxTotal = InterfaceModel::max('debit_entrant') + InterfaceModel::max('debit_sortant');
                $interface->pourcentage = $maxTotal > 0 ? round(($total / $maxTotal) * 100) : 0;
                return $interface;
            });

        return view('interface', compact('interfaces', 'routeurs', 'stats', 'topInterfaces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $routeurs = Routeur::orderBy('nom')->get();
        return view('interfaces.create', compact('routeurs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'routeur_id' => 'required|exists:routeurs,id',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:ethernet,wifi,bridge,vlan',
            'adresse_mac' => 'nullable|string|max:17|unique:interface_models',
            'adresse_ip' => 'nullable|ip|unique:interface_models',
            'mask' => 'nullable|string|max:15',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'bande' => 'nullable|required_if:type,wifi|in:2.4GHz,5GHz,6GHz',
            'ssid' => 'nullable|required_if:type,wifi|string|max:255',
            'statut' => 'required|in:actif,inactif,erreur',
            'description' => 'nullable|string',
        ]);

        $interface = InterfaceModel::create([
            'routeur_id' => $request->routeur_id,
            'nom' => $request->nom,
            'type' => $request->type,
            'adresse_mac' => $request->adresse_mac,
            'adresse_ip' => $request->adresse_ip,
            'mask' => $request->mask,
            'vlan_id' => $request->vlan_id,
            'bande' => $request->bande,
            'ssid' => $request->ssid,
            'statut' => $request->statut,
            'est_active' => $request->statut == 'actif',
            'description' => $request->description,
            'rx_bytes' => 0,
            'tx_bytes' => 0,
            'rx_packets' => 0,
            'tx_packets' => 0,
            'rx_errors' => 0,
            'tx_errors' => 0,
            'rx_drops' => 0,
            'tx_drops' => 0,
        ]);

        // Notification pour nouvelle interface
        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Nouvelle interface ajoutée',
            "L'interface {$interface->nom} a été ajoutée au routeur {$interface->routeur->nom}.",
            route('interfaces.index')
        ));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'interface' => $interface]);
        }

        return redirect()->route('interfaces.index')->with('success', 'Interface ajoutée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InterfaceModel $interface)
    {
        $interface->load('routeur');
        return view('interfaces.show', compact('interface'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InterfaceModel $interface)
    {
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($interface);
        }
        
        $routeurs = Routeur::orderBy('nom')->get();
        return view('interfaces.edit', compact('interface', 'routeurs'));
    }
    /**
     * Afficher les graphiques d'une interface.
     */
    public function graph(InterfaceModel $interface)
    {
        $interface->load('routeur');

        // Données simulées si pas de collection historique existante
        $historique = collect();
        for ($i = 5; $i >= 0; $i--) {
            $historique->push([
                'heure' => now()->subHours($i)->format('H:i'),
                'debit_entrant' => round(max(0, $interface->debit_entrant * (0.6 + mt_rand(0, 40) / 100)), 1),
                'debit_sortant' => round(max(0, $interface->debit_sortant * (0.6 + mt_rand(0, 40) / 100)), 1),
            ]);
        }

        return view('interfaces.graph', compact('interface', 'historique'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InterfaceModel $interface)
    {
        $request->validate([
            'routeur_id' => 'required|exists:routeurs,id',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:ethernet,wifi,bridge,vlan',
            'adresse_mac' => 'nullable|string|max:17|unique:interface_models,adresse_mac,' . $interface->id,
            'adresse_ip' => 'nullable|ip|unique:interface_models,adresse_ip,' . $interface->id,
            'mask' => 'nullable|string|max:15',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'bande' => 'nullable|required_if:type,wifi|in:2.4GHz,5GHz,6GHz',
            'ssid' => 'nullable|required_if:type,wifi|string|max:255',
            'statut' => 'required|in:actif,inactif,erreur',
            'description' => 'nullable|string',
        ]);

        $interface->update([
            'routeur_id' => $request->routeur_id,
            'nom' => $request->nom,
            'type' => $request->type,
            'adresse_mac' => $request->adresse_mac,
            'adresse_ip' => $request->adresse_ip,
            'mask' => $request->mask,
            'vlan_id' => $request->vlan_id,
            'bande' => $request->bande,
            'ssid' => $request->ssid,
            'statut' => $request->statut,
            'est_active' => $request->statut == 'actif',
            'description' => $request->description,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'interface' => $interface]);
        }

        return redirect()->route('interfaces.index')->with('success', 'Interface mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InterfaceModel $interface)
    {
        $nom = $interface->nom;
        $routeurNom = $interface->routeur->nom;
        $interface->delete();

        // Notification pour suppression
        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Interface supprimée',
            "L'interface {$nom} du routeur {$routeurNom} a été supprimée.",
            route('interfaces.index')
        ));

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('interfaces.index')->with('success', 'Interface supprimée avec succès.');
    }

    /**
     * Activer/Désactiver une interface.
     */
    public function toggle(InterfaceModel $interface)
    {
        try {
            $interface->est_active = !$interface->est_active;
            $interface->statut = $interface->est_active ? 'actif' : 'inactif';
            $interface->save();

            // Notification pour activation/désactivation
            $action = $interface->est_active ? 'activée' : 'désactivée';
            $routeurNom = $interface->routeur?->nom ?? 'Inconnu';
            
            Auth::user()->notify(new \App\Notifications\GenericNotification(
                "Interface {$action}",
                "L'interface {$interface->nom} du routeur {$routeurNom} a été {$action}.",
                route('interfaces.index')
            ));

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true, 
                    'est_active' => $interface->est_active,
                    'statut' => $interface->statut
                ]);
            }

            return redirect()->route('interfaces.index')->with('success', 
                'Interface ' . ($interface->est_active ? 'activée' : 'désactivée') . ' avec succès.');
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les statistiques de trafic (simulé).
     */
    public function updateTraffic(InterfaceModel $interface)
    {
        // Simuler la mise à jour du trafic
        $interface->update([
            'debit_entrant' => rand(1, 100) + (rand(0, 99) / 10),
            'debit_sortant' => rand(1, 50) + (rand(0, 99) / 10),
            'rx_bytes' => $interface->rx_bytes + rand(1000000, 10000000),
            'tx_bytes' => $interface->tx_bytes + rand(500000, 5000000),
            'rx_packets' => $interface->rx_packets + rand(1000, 10000),
            'tx_packets' => $interface->tx_packets + rand(500, 5000),
        ]);

        return response()->json(['success' => true, 'interface' => $interface]);
    }

    /**
     * Obtenir les statistiques pour le dashboard.
     */
    public function getStats()
    {
        $stats = [
            'totales' => InterfaceModel::count(),
            'actives' => InterfaceModel::where('statut', 'actif')->count(),
            'par_type' => [
                'ethernet' => InterfaceModel::where('type', 'ethernet')->count(),
                'wifi' => InterfaceModel::where('type', 'wifi')->count(),
                'bridge' => InterfaceModel::where('type', 'bridge')->count(),
                'vlan' => InterfaceModel::where('type', 'vlan')->count(),
            ],
            'debit_total' => InterfaceModel::sum('debit_entrant') + InterfaceModel::sum('debit_sortant'),
            'erreurs' => InterfaceModel::sum('rx_errors') + InterfaceModel::sum('tx_errors'),
        ];

        return response()->json($stats);
    }

    /**
     * Restaurer une interface supprimée.
     */
    public function restore($id)
    {
        $interface = InterfaceModel::withTrashed()->findOrFail($id);
        $interface->restore();

        return redirect()->route('interfaces.index')->with('success', 'Interface restaurée avec succès.');
    }

    /**
     * Vérifier la disponibilité d'une adresse MAC.
     */
    public function checkMac(Request $request)
    {
        $request->validate(['adresse_mac' => 'required|string|max:17']);
        
        $exists = InterfaceModel::where('adresse_mac', $request->adresse_mac)
                        ->when($request->id, function($query) use ($request) {
                            return $query->where('id', '!=', $request->id);
                        })
                        ->exists();

        return response()->json(['available' => !$exists]);
    }

    /**
     * Vérifier la disponibilité d'une adresse IP.
     */
    public function checkIp(Request $request)
    {
        $request->validate(['adresse_ip' => 'required|ip']);
        
        $exists = InterfaceModel::where('adresse_ip', $request->adresse_ip)
                        ->when($request->id, function($query) use ($request) {
                            return $query->where('id', '!=', $request->id);
                        })
                        ->exists();

        return response()->json(['available' => !$exists]);
    }
}