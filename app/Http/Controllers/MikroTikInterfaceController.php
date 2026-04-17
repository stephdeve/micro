<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use Illuminate\Http\Request;
use App\Services\MikrotikService;

class MikroTikInterfaceController extends Controller
{
    private MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->middleware('auth');
        $this->mikrotik = $mikrotik;
    }

    /**
     * Afficher toutes les interfaces d'un routeur
     */
    public function index(Routeur $routeur)
    {
        // Récupérer les interfaces depuis la base
        $interfacesDb = $routeur->interfaces()->get();

        // Synchroniser avec MikroTik si possible (avec timeout court)
        if ($routeur->statut === 'en_ligne') {
            try {
                set_time_limit(10); // Timeout de 10 secondes pour cette opération
                $interfacesMikrotik = $this->mikrotik->discoverInterfacesRaw($routeur);
                // Mettre à jour la base avec les infos temps réel
                foreach ($interfacesMikrotik as $iface) {
                    $routeur->interfaces()->updateOrCreate(
                        ['nom' => $iface['name']],
                        [
                            'mikrotik_id' => $iface['id'],
                            'type' => $iface['type'],
                            'adresse_mac' => $iface['mac_address'],
                            'adresse_ip' => $iface['address'],
                            'statut' => $iface['running'] ? 'actif' : 'inactif',
                            'est_active' => $iface['running'],
                            'debit_entrant' => $iface['rx_byte'],
                            'debit_sortant' => $iface['tx_byte'],
                        ]
                    );
                }
            } catch (\Throwable $e) {
                // Continuer avec les données de la base
            }
            set_time_limit(60); // Remettre le timeout par défaut
        }

        // Recharger les interfaces mises à jour
        $interfaces = $routeur->interfaces()->get();

        // Récupérer les IPs de chaque interface depuis MikroTik
        $interfacesWithIps = [];
        foreach ($interfaces as $interface) {
            $ips = [];
            if ($routeur->statut === 'en_ligne') {
                try {
                    set_time_limit(5); // Timeout court pour chaque requête IP
                    $ips = $this->mikrotik->getInterfaceIps($routeur, $interface->nom);
                } catch (\Throwable $e) {
                    // Ignorer les erreurs pour cette interface
                }
                set_time_limit(60);
            }
            $interface->ip_addresses = $ips;
            $interfacesWithIps[] = $interface;
        }

        return view('reseau.interfaces', compact('routeur', 'interfacesWithIps'));
    }

    /**
     * Activer une interface
     */
    public function enable(Request $request, Routeur $routeur, string $interfaceId)
    {
        try {
            set_time_limit(5);

            // Check if router is online
            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne. Impossible d\'activer l\'interface.'
                ], 400);
            }

            // Get interface from DB to use mikrotik_id
            $interface = $routeur->interfaces()->where('nom', $interfaceId)->first();
            $apiInterfaceId = $interface?->mikrotik_id ?? $interfaceId;

            $success = $this->mikrotik->enableInterface($routeur, $apiInterfaceId);

            // Mettre à jour la base
            if ($success) {
                $routeur->interfaces()
                    ->where('nom', $request->input('name'))
                    ->update(['statut' => 'actif', 'est_active' => true]);
            }

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Interface activée avec succès' : 'Échec de l\'activation sur MikroTik'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Désactiver une interface
     */
    public function disable(Request $request, Routeur $routeur, string $interfaceId)
    {
        try {
            set_time_limit(5);

            // Check if router is online
            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne. Impossible de désactiver l\'interface.'
                ], 400);
            }

            // Get interface from DB to use mikrotik_id
            $interface = $routeur->interfaces()->where('nom', $interfaceId)->first();
            $apiInterfaceId = $interface?->mikrotik_id ?? $interfaceId;

            $success = $this->mikrotik->disableInterface($routeur, $apiInterfaceId);

            // Mettre à jour la base
            if ($success) {
                $routeur->interfaces()
                    ->where('nom', $request->input('name'))
                    ->update(['statut' => 'inactif', 'est_active' => false]);
            }

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Interface désactivée avec succès' : 'Échec de la désactivation sur MikroTik'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renommer une interface
     */
    public function rename(Request $request, Routeur $routeur, string $interfaceId)
    {
        try {
            set_time_limit(5);

            // Check if router is online
            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne. Impossible de renommer l\'interface.'
                ], 400);
            }

            $request->validate(['name' => 'required|string|max:50']);
            $newName = $request->input('name');

            // Get interface from DB to use mikrotik_id
            $interface = $routeur->interfaces()->where('nom', $interfaceId)->first();
            $apiInterfaceId = $interface?->mikrotik_id ?? $interfaceId;

            $success = $this->mikrotik->renameInterface($routeur, $apiInterfaceId, $newName);

            // Mettre à jour la base
            if ($success) {
                $routeur->interfaces()
                    ->where('nom', $request->input('old_name'))
                    ->update(['nom' => $newName]);
            }

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Interface renommée en ' . $newName : 'Échec du renommage sur MikroTik'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Configurer MTU, commentaire
     */
    public function configure(Request $request, Routeur $routeur, string $interfaceId)
    {
        try {
            set_time_limit(5);

            // Check if router is online
            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne. Impossible de configurer l\'interface.'
                ], 400);
            }

            $request->validate([
                'mtu' => 'nullable|integer|min:64|max:9000',
                'l2mtu' => 'nullable|integer|min|64|max:9000',
                'comment' => 'nullable|string|max:255'
            ]);

            // Get interface from DB to use mikrotik_id
            $interface = $routeur->interfaces()->where('nom', $interfaceId)->first();
            $apiInterfaceId = $interface?->mikrotik_id ?? $interfaceId;

            $config = [
                'mtu' => $request->input('mtu'),
                'l2mtu' => $request->input('l2mtu'),
                'comment' => $request->input('comment')
            ];

            $success = $this->mikrotik->configureInterface($routeur, $apiInterfaceId, $config);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Configuration appliquée avec succès' : 'Échec de la configuration sur MikroTik'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assigner une adresse IP
     */
    public function setIp(Request $request, Routeur $routeur, string $interfaceName)
    {
        try {
            set_time_limit(5);

            // Check if router is online
            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne. Impossible d\'assigner une IP.'
                ], 400);
            }

            $request->validate([
                'ip' => 'required|string|regex:/^\d+\.\d+\.\d+\.\d+\/\d+$/',
                'network' => 'nullable|ip'
            ]);

            $ip = $request->input('ip');
            $network = $request->input('network');

            // For IP assignment, we use the interface name (not mikrotik_id) as the API expects name
            $success = $this->mikrotik->setInterfaceIp($routeur, $interfaceName, $ip, $network);

            // Mettre à jour la base
            if ($success) {
                $routeur->interfaces()
                    ->where('nom', $interfaceName)
                    ->update(['adresse_ip' => explode('/', $ip)[0]]);
            }

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Adresse IP assignée: ' . $ip : 'Échec de l\'assignation sur MikroTik'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une adresse IP
     */
    public function removeIp(Routeur $routeur, string $addressId)
    {
        try {
            set_time_limit(5);

            // Check if router is online
            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne. Impossible de supprimer l\'IP.'
                ], 400);
            }

            $success = $this->mikrotik->removeInterfaceIp($routeur, $addressId);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Adresse IP supprimée' : 'Échec de la suppression sur MikroTik'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une interface
     */
    public function details(Routeur $routeur, string $interfaceId)
    {
        try {
            set_time_limit(5);

            // Check if router is online
            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne. Impossible de récupérer les détails.'
                ], 400);
            }

            // Get interface from DB to use mikrotik_id
            $interface = $routeur->interfaces()->where('nom', $interfaceId)->first();
            $apiInterfaceId = $interface?->mikrotik_id ?? $interfaceId;

            $details = $this->mikrotik->getInterfaceDetails($routeur, $apiInterfaceId);

            if (!$details) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de récupérer les détails de l\'interface sur MikroTik'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'interface' => $details
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rafraîchir la liste des interfaces depuis MikroTik
     */
    public function sync(Routeur $routeur)
    {
        try {
            $interfaces = $this->mikrotik->discoverInterfacesRaw($routeur);

            // Mettre à jour la base
            foreach ($interfaces as $iface) {
                $routeur->interfaces()->updateOrCreate(
                    ['nom' => $iface['name']],
                    [
                        'mikrotik_id' => $iface['id'],
                        'type' => $iface['type'],
                        'adresse_mac' => $iface['mac_address'],
                        'adresse_ip' => $iface['address'],
                        'statut' => $iface['running'] ? 'actif' : 'inactif',
                        'est_active' => $iface['running'],
                        'debit_entrant' => $iface['rx_byte'],
                        'debit_sortant' => $iface['tx_byte'],
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => count($interfaces) . ' interfaces synchronisées',
                'interfaces' => $interfaces
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques temps réel d'une interface (RX/TX) — utilisé par les mini-graphiques
     */
    public function realtimeStats(Routeur $routeur, string $interfaceName)
    {
        try {
            set_time_limit(8);

            if ($routeur->statut !== 'en_ligne') {
                return response()->json([
                    'success' => false,
                    'message' => 'Routeur hors ligne',
                    'name' => $interfaceName,
                    'rx_bytes' => 0,
                    'tx_bytes' => 0,
                    'rx_mbps' => 0,
                    'tx_mbps' => 0,
                ]);
            }

            $stats = $this->mikrotik->getInterfaceTraffic($routeur, $interfaceName);

            // Mettre à jour la base si succès
            if ($stats['success'] ?? false) {
                $routeur->interfaces()
                    ->where('nom', $interfaceName)
                    ->update([
                        'debit_entrant' => $stats['rx_bytes'],
                        'debit_sortant' => $stats['tx_bytes'],
                        'statut' => ($stats['running'] ?? false) ? 'actif' : 'inactif',
                        'est_active' => ($stats['running'] ?? false),
                    ]);
            }

            return response()->json($stats);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage(),
                'name' => $interfaceName,
                'rx_bytes' => 0,
                'tx_bytes' => 0,
                'rx_mbps' => 0,
                'tx_mbps' => 0,
            ], 500);
        }
    }
}
