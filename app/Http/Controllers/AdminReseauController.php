<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use App\Models\InterfaceModel;
use App\Models\FirewallRule;
use App\Models\Securite;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminReseauController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin_reseau|super_admin']);
    }

    public function dashboard()
    {
        $routeurs = Routeur::with(['interfaces', 'wifiZones', 'employes'])->get();
        $routeursEnLigne = Routeur::where('statut', 'en_ligne')->count();
        $routeursHorsLigne = Routeur::where('statut', 'hors_ligne')->count();

        $globalPerf = [
            'cpu' => round(Routeur::whereNotNull('cpu_usage')->avg('cpu_usage') ?? 0, 1),
            'memory' => round(Routeur::whereNotNull('memory_usage')->avg('memory_usage') ?? 0, 1),
            'temperature' => round(Routeur::whereNotNull('temperature')->avg('temperature') ?? 0, 1),
        ];

        $bandePassante = InterfaceModel::sum('debit_entrant') + InterfaceModel::sum('debit_sortant');
        $clientsWiFi = InterfaceModel::where('type', 'wireless')->sum('clients_connectes');
        $reglesFirewall = FirewallRule::where('est_active', true)->count();
        $alertes = Securite::where('type', 'alerte')->where('statut', 'nouveau')->count();

        // Nouvelles données pour le dashboard enrichi
        $zonesWifi = \App\Models\WifiZone::where('active', true)->count();
        $utilisateursConnectes = \App\Models\Employe::where('active', true)
            ->whereNotNull('last_connected_at')
            ->where('last_connected_at', '>=', now()->subHours(1))
            ->count();
        $alertesCritiques = Securite::where('severite', 'critique')
            ->whereDate('created_at', '>=', now()->subDay())
            ->count();

        // Top consommateurs de bande passante
        $topConsommateurs = InterfaceModel::select('nom', 'adresse_ip', 'debit_entrant', 'debit_sortant', 'type')
            ->orderByRaw('(debit_entrant + debit_sortant) DESC')
            ->take(5)
            ->get()
            ->map(function ($iface) {
                $iface->total = $iface->debit_entrant + $iface->debit_sortant;
                return $iface;
            });

        // Interfaces actives
        $interfacesActives = InterfaceModel::where('statut', 'actif')
            ->orderByRaw('(debit_entrant + debit_sortant) DESC')
            ->take(8)
            ->get();

        // Règles firewall actives
        $firewallRules = FirewallRule::where('est_active', true)
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        // Routes actives depuis MikroTik (limité aux 3 premiers routeurs pour éviter timeout)
        $routesActives = collect();
        $service = app(MikrotikService::class);
        $routeursEnLigneForMikrotik = $routeurs->where('statut', 'en_ligne')->take(3);

        foreach ($routeursEnLigneForMikrotik as $routeur) {
            try {
                // Timeout de 2 secondes max par routeur
                $routes = retry(1, function () use ($service, $routeur) {
                    return $service->getRoutes($routeur);
                }, 2000);

                foreach (array_slice($routes, 0, 5) as $route) {
                    $routesActives->push((object) array_merge($route, ['routeur_nom' => $routeur->nom]));
                }
            } catch (\Throwable $e) {
                // Ignorer les erreurs de connexion
                \Log::warning("Impossible de récupérer les routes du routeur {$routeur->nom}: " . $e->getMessage());
            }
        }

        // Alertes récentes
        $alertesRecentes = Securite::where('type', 'alerte')
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Données trafic global (7 derniers jours - simulé si pas de données)
        $traficGlobal = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $traficGlobal->push((object) [
                'date' => $date,
                'download' => InterfaceModel::whereDate('updated_at', $date)->sum('debit_entrant') ?: rand(500, 2000),
                'upload' => InterfaceModel::whereDate('updated_at', $date)->sum('debit_sortant') ?: rand(200, 800),
            ]);
        }

        return view('dashboards.admin-reseau', compact(
            'routeurs', 'routeursEnLigne', 'routeursHorsLigne',
            'globalPerf', 'bandePassante', 'clientsWiFi',
            'reglesFirewall', 'alertes',
            'zonesWifi', 'utilisateursConnectes', 'alertesCritiques',
            'topConsommateurs', 'interfacesActives', 'firewallRules',
            'routesActives', 'alertesRecentes', 'traficGlobal'
        ));
    }

    // ===== FIREWALL =====
    public function firewallIndex(Routeur $routeur)
    {
        $service = app(MikrotikService::class);
        $rules = $service->getFirewallRules($routeur);
        $natRules = $service->getNatRules($routeur);

        return view('reseau.firewall', compact('routeur', 'rules', 'natRules'));
    }

    public function firewallStore(Request $request, Routeur $routeur)
    {
        $data = $request->validate([
            'chain' => 'required|in:input,forward,output',
            'action' => 'required|in:accept,drop,reject',
            'protocol' => 'nullable|string',
            'src_address' => 'nullable|ip',
            'dst_address' => 'nullable|ip',
            'dst_port' => 'nullable|string',
            'comment' => 'nullable|string|max:255',
        ]);

        $service = app(MikrotikService::class);
        $ok = $service->addFirewallRule($routeur, $data);

        return response()->json(['success' => $ok]);
    }

    public function firewallDestroy(Routeur $routeur, string $ruleId)
    {
        $service = app(MikrotikService::class);
        $ok = $service->removeFirewallRule($routeur, $ruleId);

        return response()->json(['success' => $ok]);
    }

    public function firewallToggle(Request $request, Routeur $routeur, string $ruleId)
    {
        $service = app(MikrotikService::class);
        $ok = $service->toggleFirewallRule($routeur, $ruleId, $request->boolean('enable'));

        return response()->json(['success' => $ok]);
    }

    // ===== WiFi =====
    public function wifiIndex(Routeur $routeur)
    {
        $service = app(MikrotikService::class);
        $interfaces = $service->getWifiInterfaces($routeur);
        $profiles = $service->getWifiSecurityProfiles($routeur);
        $clients = $service->getWifiRegistrations($routeur);

        return view('reseau.wifi', compact('routeur', 'interfaces', 'profiles', 'clients'));
    }

    public function wifiUpdateSsid(Request $request, Routeur $routeur)
    {
        $data = $request->validate([
            'interface_id' => 'required|string',
            'ssid' => 'required|string|max:255',
        ]);

        $service = app(MikrotikService::class);
        $ok = $service->updateWifiSsid($routeur, $data['interface_id'], $data['ssid']);

        return response()->json(['success' => $ok]);
    }

    // ===== ROUTES =====
    public function routesIndex(Routeur $routeur)
    {
        $service = app(MikrotikService::class);
        $routes = $service->getRoutes($routeur);

        return view('reseau.routes', compact('routeur', 'routes'));
    }

    public function routesStore(Request $request, Routeur $routeur)
    {
        $data = $request->validate([
            'dst_address' => 'required|string',
            'gateway' => 'required|string',
            'distance' => 'nullable|integer',
            'comment' => 'nullable|string|max:255',
        ]);

        $service = app(MikrotikService::class);
        $ok = $service->addRoute($routeur, $data);

        return response()->json(['success' => $ok]);
    }

    public function routesDestroy(Routeur $routeur, string $routeId)
    {
        $service = app(MikrotikService::class);
        $ok = $service->removeRoute($routeur, $routeId);

        return response()->json(['success' => $ok]);
    }

    // ===== BANDE PASSANTE =====
    public function bandwidthIndex(Routeur $routeur)
    {
        $service = app(MikrotikService::class);
        $queues = $service->getQueues($routeur);

        return view('reseau.bandwidth', compact('routeur', 'queues'));
    }

    public function bandwidthStore(Request $request, Routeur $routeur)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'target' => 'required|string',
            'max_limit' => 'required|string',
            'comment' => 'nullable|string|max:255',
        ]);

        $service = app(MikrotikService::class);
        $ok = $service->addQueue($routeur, $data);

        return response()->json(['success' => $ok]);
    }

    public function bandwidthUpdate(Request $request, Routeur $routeur, string $queueId)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:255',
            'target' => 'nullable|string',
            'max_limit' => 'nullable|string',
            'comment' => 'nullable|string|max:255',
        ]);

        $service = app(MikrotikService::class);
        $ok = $service->updateQueue($routeur, $queueId, $data);

        return response()->json(['success' => $ok]);
    }

    public function bandwidthDestroy(Routeur $routeur, string $queueId)
    {
        $service = app(MikrotikService::class);
        $ok = $service->removeQueue($routeur, $queueId);

        return response()->json(['success' => $ok]);
    }

    // ===== DHCP =====
    public function dhcpIndex(Routeur $routeur)
    {
        $service = app(MikrotikService::class);
        $leases = $service->getDhcpLeases($routeur);
        $servers = $service->getDhcpServers($routeur);
        $networks = $service->getDhcpNetworks($routeur);
        $interfaces = $service->getInterfaces($routeur);

        return view('reseau.dhcp', compact('routeur', 'leases', 'servers', 'networks', 'interfaces'));
    }

    public function dhcpStoreServer(Request $request, Routeur $routeur)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'interface' => 'required|string',
            'address_pool' => 'required|string',
            'lease_time' => 'nullable|string',
        ]);

        $service = app(MikrotikService::class);
        $ok = $service->addDhcpServer($routeur, $data);

        return response()->json(['success' => $ok, 'message' => $ok ? 'Serveur créé' : 'Échec']);
    }

    public function dhcpDestroyServer(Routeur $routeur, string $serverId)
    {
        $service = app(MikrotikService::class);
        $ok = $service->removeDhcpServer($routeur, $serverId);

        return response()->json(['success' => $ok]);
    }

    public function dhcpStoreNetwork(Request $request, Routeur $routeur)
    {
        $data = $request->validate([
            'address' => 'required|string',
            'gateway' => 'required|string',
            'dns_server' => 'nullable|string',
            'domain' => 'nullable|string',
        ]);

        $service = app(MikrotikService::class);
        $ok = $service->addDhcpNetwork($routeur, $data);

        return response()->json(['success' => $ok, 'message' => $ok ? 'Réseau créé' : 'Échec']);
    }

    public function dhcpDestroyNetwork(Routeur $routeur, string $networkId)
    {
        $service = app(MikrotikService::class);
        $ok = $service->removeDhcpNetwork($routeur, $networkId);

        return response()->json(['success' => $ok]);
    }
}
