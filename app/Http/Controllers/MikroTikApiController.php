<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MikroTikApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin_reseau|super_admin']);
    }

    /**
     * Tester la connexion à un routeur
     */
    public function testConnection(Request $request)
    {
        $data = $request->validate([
            'ip' => 'required|ip',
            'user' => 'required|string',
            'password' => 'required|string',
            'port' => 'nullable|integer',
        ]);

        try {
            $client = new \PEAR2\Net\RouterOS\Client(
                $data['ip'],
                $data['user'],
                $data['password'],
                $data['port'] ?? 8728
            );

            // Tester avec /system/identity
            $response = $client->sendSync(new \PEAR2\Net\RouterOS\Request('/system/identity/print'));
            $identity = null;
            foreach ($response as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $identity = $item->getProperty('name');
                    break;
                }
            }

            // Récupérer les ressources
            $resResp = $client->sendSync(new \PEAR2\Net\RouterOS\Request('/system/resource/print'));
            $resources = [];
            foreach ($resResp as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $resources = [
                        'version' => $item->getProperty('version'),
                        'uptime' => $item->getProperty('uptime'),
                        'cpu_load' => $item->getProperty('cpu-load'),
                        'free_memory' => $item->getProperty('free-memory'),
                        'total_memory' => $item->getProperty('total-memory'),
                        'free_hdd' => $item->getProperty('free-hdd-space'),
                        'total_hdd' => $item->getProperty('total-hdd-space'),
                    ];
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'identity' => $identity,
                'resources' => $resources,
                'message' => 'Connexion réussie',
            ]);
        } catch (\Exception $e) {
            Log::error('MikroTik test connection failed', [
                'ip' => $data['ip'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Récupérer les données temps réel d'un routeur
     */
    public function realtimeData(Routeur $routeur)
    {
        $service = app(MikrotikService::class);

        if (!$service->testConnection($routeur)) {
            return response()->json([
                'success' => false,
                'message' => 'Routeur hors ligne',
            ], 503);
        }

        try {
            $client = $service->client($routeur);

            // Ressources système
            $resResp = $client->sendSync(new \PEAR2\Net\RouterOS\Request('/system/resource/print'));
            $resources = [];
            foreach ($resResp as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $resources = [
                        'cpu_load' => (int) $item->getProperty('cpu-load'),
                        'free_memory' => (int) $item->getProperty('free-memory'),
                        'total_memory' => (int) $item->getProperty('total-memory'),
                        'uptime' => $item->getProperty('uptime'),
                        'version' => $item->getProperty('version'),
                    ];
                    break;
                }
            }

            // Interfaces
            $interfaces = $service->discoverInterfacesRaw($routeur, $client);

            // Clients WiFi
            $wifiClients = $service->getWifiRegistrations($routeur);

            return response()->json([
                'success' => true,
                'resources' => $resources,
                'interfaces' => $interfaces,
                'wifi_clients' => count($wifiClients),
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('MikroTik realtime data failed', [
                'routeur_id' => $routeur->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exécuter une commande sur le routeur
     */
    public function executeCommand(Request $request, Routeur $routeur)
    {
        $data = $request->validate([
            'command' => 'required|string',
            'arguments' => 'nullable|array',
        ]);

        $allowedCommands = [
            '/ping',
            '/tool/fetch',
            '/system/identity/print',
            '/interface/print',
            '/ip/address/print',
            '/ip/route/print',
        ];

        if (!in_array($data['command'], $allowedCommands)) {
            return response()->json([
                'success' => false,
                'message' => 'Commande non autorisée',
            ], 403);
        }

        try {
            $service = app(MikrotikService::class);
            $client = $service->client($routeur);

            $req = new \PEAR2\Net\RouterOS\Request($data['command']);
            
            if (!empty($data['arguments'])) {
                foreach ($data['arguments'] as $key => $value) {
                    $req->setArgument($key, $value);
                }
            }

            $response = $client->sendSync($req);
            
            $results = [];
            foreach ($response as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $results[] = $item->getAllProperties();
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
