<?php

namespace App\Services;

use App\Models\Routeur;
use App\Models\InterfaceModel;
use App\Models\Statistique;
use App\Models\Securite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Request;

class MikrotikService
{
    public function client(Routeur $routeur): Client
    {
        return new Client(
            $routeur->adresse_ip,
            $routeur->api_user,
            $routeur->api_password,
            (int) config('mikrotik.api_port', 8728)
        );
    }

    public function handshake(Routeur $routeur): bool
    {
        try {
            $client = $this->client($routeur);
            $resource = $client->sendSync(new Request('/system/resource/print'));

            $routeur->update([
                'modele' => $resource->getProperty('board-name') ?? $routeur->modele,
                'version_ros' => $resource->getProperty('version') ?? $routeur->version_ros,
                'numero_serie' => $resource->getProperty('serial-number') ?? $routeur->numero_serie,
                'statut' => 'en_ligne',
                'derniere_sync' => now(),
                'cpu_usage' => (float) ($resource->getProperty('cpu') ?? 0),
                'temperature' => (float) ($resource->getProperty('temperature') ?? 0),
                'uptime' => $this->parseUptime($resource->getProperty('uptime')),
            ]);

            $this->discoverInterfaces($routeur, $client);

            return true;
        } catch (\Throwable $e) {
            $routeur->update(['statut' => 'hors_ligne', 'derniere_sync' => now()]);
            $this->createAlert($routeur, 'critique', 'Handshake MikroTik échoué : ' . $e->getMessage());
            Log::error('Mikrotik handshake failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function discoverInterfaces(Routeur $routeur, ?Client $client = null): void
    {
        $client = $client ?: $this->client($routeur);

        try {
            $resp = $client->sendSync(new Request('/interface/print'));
            $routeur->interfaces()->delete();

            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) {
                    continue;
                }

                $name = $item->getProperty('name');
                if (! $name) {
                    continue;
                }

                $interface = InterfaceModel::updateOrCreate(
                    ['routeur_id' => $routeur->id, 'nom' => $name],
                    [
                        'type' => $item->getProperty('type') ?? 'ethernet',
                        'adresse_mac' => $item->getProperty('mac-address'),
                        'adresse_ip' => $item->getProperty('address'),
                        'statut' => ($item->getProperty('running') === 'true' ? 'actif' : 'inactif'),
                        'est_active' => ($item->getProperty('running') === 'true'),
                        'debit_entrant' => (float) ($item->getProperty('rx-byte') ?? 0),
                        'debit_sortant' => (float) ($item->getProperty('tx-byte') ?? 0),
                    ]
                );

                Statistique::create([
                    'routeur_id' => $routeur->id,
                    'interface_id' => $interface->id,
                    'timestamp' => now(),
                    'type' => 'interface_traffic',
                    'valeur' => (float) ($interface->debit_entrant + $interface->debit_sortant),
                    'unite' => 'Octets',
                    'donnees_complementaires' => ['rx' => $interface->debit_entrant, 'tx' => $interface->debit_sortant],
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Mikrotik discoverInterfaces failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            $this->createAlert($routeur, 'warn', 'Découverte interfaces échouée : ' . $e->getMessage());
        }
    }

    public function captureHeartbeat(): void
    {
        $routeurs = Routeur::where('statut', '<>', 'maintenance')->get();

        foreach ($routeurs as $routeur) {
            try {
                $client = $this->client($routeur);
                $resource = $client->sendSync(new Request('/system/resource/print'));

                $cpu = (float) ($resource->getProperty('cpu') ?? 0);
                $temp = (float) ($resource->getProperty('temperature') ?? 0);

                $routeur->update(['cpu_usage' => $cpu, 'temperature' => $temp, 'statut' => 'en_ligne', 'derniere_sync' => Carbon::now()]);

                $interfaces = $client->sendSync(new Request('/interface/print'));
                foreach ($interfaces as $face) {
                    if (! $face instanceof \PEAR2\Net\RouterOS\Response) continue;
                    if (! $face->getProperty('name')) continue;

                    $interface = InterfaceModel::firstOrCreate(
                        ['routeur_id' => $routeur->id, 'nom' => $face->getProperty('name')],
                        ['type' => $face->getProperty('type') ?? 'ethernet']
                    );

                    $rx = (int) ($face->getProperty('rx-byte') ?? 0);
                    $tx = (int) ($face->getProperty('tx-byte') ?? 0);

                    $interface->update([
                        'adresse_mac' => $face->getProperty('mac-address') ?? $interface->adresse_mac,
                        'statut' => ($face->getProperty('running') === 'true') ? 'actif' : 'inactif',
                        'est_active' => ($face->getProperty('running') === 'true'),
                        'rx_bytes' => $rx,
                        'tx_bytes' => $tx,
                    ]);

                    Statistique::create([
                        'routeur_id' => $routeur->id,
                        'interface_id' => $interface->id,
                        'timestamp' => Carbon::now(),
                        'type' => 'traffic',
                        'valeur' => (float) (($rx + $tx) / 1024),
                        'unite' => 'KB',
                        'donnees_complementaires' => ['rx' => $rx, 'tx' => $tx],
                    ]);
                }
            } catch (\Throwable $e) {
                $routeur->update(['statut' => 'hors_ligne']);
                $this->createAlert($routeur, 'critique', 'Heartbeat échoué : ' . $e->getMessage());
                Log::error('Mikrotik heartbeat error', ['routeur_id' => $routeur->id, 'message' => $e->getMessage()]);
            }
        }
    }

    public function createAlert(Routeur $routeur, string $niveau, string $message): Securite
    {
        return Securite::create([
            'routeur_id' => $routeur->id,
            'type' => 'alerte',
            'severite' => $niveau,
            'statut' => 'non_lu',
            'source_ip' => $routeur->adresse_ip,
            'description' => $message,
            'action_entreprise' => 'vérifier',
        ]);
    }

    private function parseUptime($u): int
    {
        if (empty($u)) {
            return 0;
        }

        $seconds = 0;
        if (preg_match('/(\d+)w/', $u, $m)) {
            $seconds += $m[1] * 604800;
        }
        if (preg_match('/(\d+)d/', $u, $m)) {
            $seconds += $m[1] * 86400;
        }
        if (preg_match('/(\d+)h/', $u, $m)) {
            $seconds += $m[1] * 3600;
        }
        if (preg_match('/(\d+)m/', $u, $m)) {
            $seconds += $m[1] * 60;
        }
        if (preg_match('/(\d+)s/', $u, $m)) {
            $seconds += $m[1];
        }

        return $seconds;
    }
}
