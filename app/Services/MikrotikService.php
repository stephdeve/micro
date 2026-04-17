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

    /**
     * Récupérer les interfaces en format brut pour API temps réel
     */
    public function discoverInterfacesRaw(Routeur $routeur, ?Client $client = null): array
    {
        $client = $client ?: $this->client($routeur);
        $interfaces = [];

        try {
            $resp = $client->sendSync(new Request('/interface/print'));

            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) {
                    continue;
                }

                $name = $item->getProperty('name');
                if (! $name) {
                    continue;
                }

                $interfaces[] = [
                    'id' => $item->getProperty('.id'),
                    'name' => $name,
                    'type' => $item->getProperty('type') ?? 'ethernet',
                    'mac_address' => $item->getProperty('mac-address') ?? null,
                    'address' => $item->getProperty('address') ?? null,
                    'running' => $item->getProperty('running') === 'true',
                    'rx_byte' => (int) ($item->getProperty('rx-byte') ?? 0),
                    'tx_byte' => (int) ($item->getProperty('tx-byte') ?? 0),
                    'rx_packets' => (int) ($item->getProperty('rx-packet') ?? 0),
                    'tx_packets' => (int) ($item->getProperty('tx-packet') ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            Log::error('Mikrotik discoverInterfacesRaw failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
        }

        return $interfaces;
    }

    /**
     * Alias pour récupérer les interfaces (utilisé par les contrôleurs)
     */
    public function getInterfaces(Routeur $routeur): array
    {
        return $this->discoverInterfacesRaw($routeur);
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

    public function createAlert(Routeur $routeur, string $niveau, string $message, string $nomEvenement = 'Alerte réseau'): Securite
    {
        return Securite::create([
            'routeur_id' => $routeur->id,
            'nom_evenement' => $nomEvenement,
            'type' => 'alerte',
            'severite' => $niveau,
            'statut' => 'nouveau',
            'source_ip' => $routeur->adresse_ip,
            'description' => $message,
            'action_entreprise' => 'vérifier',
        ]);
    }

    // ===== FIREWALL =====

    public function getFirewallRules(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/firewall/filter/print'));

            $rules = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $rules[] = [
                    'id' => $item->getProperty('.id'),
                    'chain' => $item->getProperty('chain'),
                    'action' => $item->getProperty('action'),
                    'protocol' => $item->getProperty('protocol'),
                    'src_address' => $item->getProperty('src-address'),
                    'dst_address' => $item->getProperty('dst-address'),
                    'src_port' => $item->getProperty('src-port'),
                    'dst_port' => $item->getProperty('dst-port'),
                    'in_interface' => $item->getProperty('in-interface'),
                    'out_interface' => $item->getProperty('out-interface'),
                    'comment' => $item->getProperty('comment'),
                    'disabled' => $item->getProperty('disabled') === 'true',
                    'bytes' => (int) ($item->getProperty('bytes') ?? 0),
                    'packets' => (int) ($item->getProperty('packets') ?? 0),
                ];
            }
            return $rules;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getFirewallRules failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function addFirewallRule(Routeur $routeur, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/filter/add');
            $req->setArgument('chain', $data['chain'] ?? 'forward');
            $req->setArgument('action', $data['action'] ?? 'accept');

            if (!empty($data['protocol'])) $req->setArgument('protocol', $data['protocol']);
            if (!empty($data['src_address'])) $req->setArgument('src-address', $data['src_address']);
            if (!empty($data['dst_address'])) $req->setArgument('dst-address', $data['dst_address']);
            if (!empty($data['dst_port'])) $req->setArgument('dst-port', $data['dst_port']);
            if (!empty($data['comment'])) $req->setArgument('comment', $data['comment']);

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik addFirewallRule failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeFirewallRule(Routeur $routeur, string $ruleId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/filter/remove');
            $req->setArgument('numbers', $ruleId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeFirewallRule failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function toggleFirewallRule(Routeur $routeur, string $ruleId, bool $enable): bool
    {
        try {
            $client = $this->client($routeur);
            $cmd = $enable ? '/ip/firewall/filter/enable' : '/ip/firewall/filter/disable';
            $req = new Request($cmd);
            $req->setArgument('numbers', $ruleId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik toggleFirewallRule failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Mettre à jour une règle firewall (Filter)
     */
    public function updateFirewallRule(Routeur $routeur, string $ruleId, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/filter/set');
            $req->setArgument('numbers', $ruleId);

            if (isset($data['chain'])) $req->setArgument('chain', $data['chain']);
            if (isset($data['action'])) $req->setArgument('action', $data['action']);
            if (isset($data['protocol'])) $req->setArgument('protocol', $data['protocol']);
            if (isset($data['src_address'])) $req->setArgument('src-address', $data['src_address']);
            if (isset($data['dst_address'])) $req->setArgument('dst-address', $data['dst_address']);
            if (isset($data['src_port'])) $req->setArgument('src-port', $data['src_port']);
            if (isset($data['dst_port'])) $req->setArgument('dst-port', $data['dst_port']);
            if (isset($data['in_interface'])) $req->setArgument('in-interface', $data['in_interface']);
            if (isset($data['out_interface'])) $req->setArgument('out-interface', $data['out_interface']);
            if (isset($data['comment'])) $req->setArgument('comment', $data['comment']);

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik updateFirewallRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Réordonner une règle (déplacer vers une position)
     */
    public function moveFirewallRule(Routeur $routeur, string $ruleId, string $destination): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/filter/move');
            $req->setArgument('numbers', $ruleId);
            $req->setArgument('destination', $destination);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik moveFirewallRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ==================== NAT RULES ====================

    /**
     * Récupérer toutes les règles NAT
     */
    public function getNatRules(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/firewall/nat/print'));

            $rules = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;

                $rules[] = [
                    'id' => $item->getProperty('.id'),
                    'chain' => $item->getProperty('chain') ?? 'srcnat',
                    'action' => $item->getProperty('action') ?? 'masquerade',
                    'protocol' => $item->getProperty('protocol'),
                    'src_address' => $item->getProperty('src-address'),
                    'dst_address' => $item->getProperty('dst-address'),
                    'src_port' => $item->getProperty('src-port'),
                    'dst_port' => $item->getProperty('dst-port'),
                    'to_addresses' => $item->getProperty('to-addresses'),
                    'to_ports' => $item->getProperty('to-ports'),
                    'comment' => $item->getProperty('comment'),
                    'disabled' => $item->getProperty('disabled') === 'true',
                    'out_interface' => $item->getProperty('out-interface'),
                    'in_interface' => $item->getProperty('in-interface'),
                    'bytes' => (int) ($item->getProperty('bytes') ?? 0),
                    'packets' => (int) ($item->getProperty('packets') ?? 0),
                ];
            }

            return $rules;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getNatRules failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Ajouter une règle NAT
     */
    public function addNatRule(Routeur $routeur, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/nat/add');

            $req->setArgument('chain', $data['chain'] ?? 'srcnat');
            $req->setArgument('action', $data['action'] ?? 'masquerade');

            if (!empty($data['protocol'])) $req->setArgument('protocol', $data['protocol']);
            if (!empty($data['src_address'])) $req->setArgument('src-address', $data['src_address']);
            if (!empty($data['dst_address'])) $req->setArgument('dst-address', $data['dst_address']);
            if (!empty($data['src_port'])) $req->setArgument('src-port', $data['src_port']);
            if (!empty($data['dst_port'])) $req->setArgument('dst-port', $data['dst_port']);
            if (!empty($data['to_addresses'])) $req->setArgument('to-addresses', $data['to_addresses']);
            if (!empty($data['to_ports'])) $req->setArgument('to-ports', $data['to_ports']);
            if (!empty($data['comment'])) $req->setArgument('comment', $data['comment']);
            if (!empty($data['out_interface'])) $req->setArgument('out-interface', $data['out_interface']);
            if (!empty($data['in_interface'])) $req->setArgument('in-interface', $data['in_interface']);

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik addNatRule failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Modifier une règle NAT
     */
    public function updateNatRule(Routeur $routeur, string $ruleId, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/nat/set');
            $req->setArgument('numbers', $ruleId);

            if (isset($data['chain'])) $req->setArgument('chain', $data['chain']);
            if (isset($data['action'])) $req->setArgument('action', $data['action']);
            if (isset($data['protocol'])) $req->setArgument('protocol', $data['protocol']);
            if (isset($data['src_address'])) $req->setArgument('src-address', $data['src_address']);
            if (isset($data['dst_address'])) $req->setArgument('dst-address', $data['dst_address']);
            if (isset($data['src_port'])) $req->setArgument('src-port', $data['src_port']);
            if (isset($data['dst_port'])) $req->setArgument('dst-port', $data['dst_port']);
            if (isset($data['to_addresses'])) $req->setArgument('to-addresses', $data['to_addresses']);
            if (isset($data['to_ports'])) $req->setArgument('to-ports', $data['to_ports']);
            if (isset($data['comment'])) $req->setArgument('comment', $data['comment']);
            if (isset($data['out_interface'])) $req->setArgument('out-interface', $data['out_interface']);
            if (isset($data['in_interface'])) $req->setArgument('in-interface', $data['in_interface']);

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik updateNatRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Supprimer une règle NAT
     */
    public function removeNatRule(Routeur $routeur, string $ruleId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/nat/remove');
            $req->setArgument('numbers', $ruleId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeNatRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Activer/Désactiver une règle NAT
     */
    public function toggleNatRule(Routeur $routeur, string $ruleId, bool $enable): bool
    {
        try {
            $client = $this->client($routeur);
            $cmd = $enable ? '/ip/firewall/nat/enable' : '/ip/firewall/nat/disable';
            $req = new Request($cmd);
            $req->setArgument('numbers', $ruleId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik toggleNatRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Déplacer une règle NAT
     */
    public function moveNatRule(Routeur $routeur, string $ruleId, string $destination): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/nat/move');
            $req->setArgument('numbers', $ruleId);
            $req->setArgument('destination', $destination);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik moveNatRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ==================== MANGLE RULES ====================

    /**
     * Récupérer toutes les règles Mangle
     */
    public function getMangleRules(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/firewall/mangle/print'));

            $rules = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;

                $rules[] = [
                    'id' => $item->getProperty('.id'),
                    'chain' => $item->getProperty('chain') ?? 'prerouting',
                    'action' => $item->getProperty('action') ?? 'accept',
                    'protocol' => $item->getProperty('protocol'),
                    'src_address' => $item->getProperty('src-address'),
                    'dst_address' => $item->getProperty('dst-address'),
                    'src_port' => $item->getProperty('src-port'),
                    'dst_port' => $item->getProperty('dst-port'),
                    'new_routing_mark' => $item->getProperty('new-routing-mark'),
                    'new_connection_mark' => $item->getProperty('new-connection-mark'),
                    'new_packet_mark' => $item->getProperty('new-packet-mark'),
                    'passthrough' => $item->getProperty('passthrough') === 'true',
                    'comment' => $item->getProperty('comment'),
                    'disabled' => $item->getProperty('disabled') === 'true',
                    'in_interface' => $item->getProperty('in-interface'),
                    'out_interface' => $item->getProperty('out-interface'),
                    'bytes' => (int) ($item->getProperty('bytes') ?? 0),
                    'packets' => (int) ($item->getProperty('packets') ?? 0),
                ];
            }

            return $rules;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getMangleRules failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Ajouter une règle Mangle
     */
    public function addMangleRule(Routeur $routeur, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/mangle/add');

            $req->setArgument('chain', $data['chain'] ?? 'prerouting');
            $req->setArgument('action', $data['action'] ?? 'accept');

            if (!empty($data['protocol'])) $req->setArgument('protocol', $data['protocol']);
            if (!empty($data['src_address'])) $req->setArgument('src-address', $data['src_address']);
            if (!empty($data['dst_address'])) $req->setArgument('dst-address', $data['dst_address']);
            if (!empty($data['src_port'])) $req->setArgument('src-port', $data['src_port']);
            if (!empty($data['dst_port'])) $req->setArgument('dst-port', $data['dst_port']);
            if (!empty($data['new_routing_mark'])) $req->setArgument('new-routing-mark', $data['new_routing_mark']);
            if (!empty($data['new_connection_mark'])) $req->setArgument('new-connection-mark', $data['new_connection_mark']);
            if (!empty($data['new_packet_mark'])) $req->setArgument('new-packet-mark', $data['new_packet_mark']);
            if (!empty($data['comment'])) $req->setArgument('comment', $data['comment']);
            if (!empty($data['in_interface'])) $req->setArgument('in-interface', $data['in_interface']);
            if (!empty($data['out_interface'])) $req->setArgument('out-interface', $data['out_interface']);
            if (isset($data['passthrough'])) $req->setArgument('passthrough', $data['passthrough'] ? 'yes' : 'no');

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik addMangleRule failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Modifier une règle Mangle
     */
    public function updateMangleRule(Routeur $routeur, string $ruleId, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/mangle/set');
            $req->setArgument('numbers', $ruleId);

            if (isset($data['chain'])) $req->setArgument('chain', $data['chain']);
            if (isset($data['action'])) $req->setArgument('action', $data['action']);
            if (isset($data['protocol'])) $req->setArgument('protocol', $data['protocol']);
            if (isset($data['src_address'])) $req->setArgument('src-address', $data['src_address']);
            if (isset($data['dst_address'])) $req->setArgument('dst-address', $data['dst_address']);
            if (isset($data['src_port'])) $req->setArgument('src-port', $data['src_port']);
            if (isset($data['dst_port'])) $req->setArgument('dst-port', $data['dst_port']);
            if (isset($data['new_routing_mark'])) $req->setArgument('new-routing-mark', $data['new_routing_mark']);
            if (isset($data['new_connection_mark'])) $req->setArgument('new-connection-mark', $data['new_connection_mark']);
            if (isset($data['new_packet_mark'])) $req->setArgument('new-packet-mark', $data['new_packet_mark']);
            if (isset($data['comment'])) $req->setArgument('comment', $data['comment']);
            if (isset($data['in_interface'])) $req->setArgument('in-interface', $data['in_interface']);
            if (isset($data['out_interface'])) $req->setArgument('out-interface', $data['out_interface']);
            if (isset($data['passthrough'])) $req->setArgument('passthrough', $data['passthrough'] ? 'yes' : 'no');

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik updateMangleRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Supprimer une règle Mangle
     */
    public function removeMangleRule(Routeur $routeur, string $ruleId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/mangle/remove');
            $req->setArgument('numbers', $ruleId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeMangleRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Activer/Désactiver une règle Mangle
     */
    public function toggleMangleRule(Routeur $routeur, string $ruleId, bool $enable): bool
    {
        try {
            $client = $this->client($routeur);
            $cmd = $enable ? '/ip/firewall/mangle/enable' : '/ip/firewall/mangle/disable';
            $req = new Request($cmd);
            $req->setArgument('numbers', $ruleId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik toggleMangleRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Déplacer une règle Mangle
     */
    public function moveMangleRule(Routeur $routeur, string $ruleId, string $destination): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/firewall/mangle/move');
            $req->setArgument('numbers', $ruleId);
            $req->setArgument('destination', $destination);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik moveMangleRule failed', ['routeur_id' => $routeur->id, 'rule_id' => $ruleId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ===== WiFi =====

    public function getWifiInterfaces(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/interface/wireless/print'));

            $interfaces = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $interfaces[] = [
                    'id' => $item->getProperty('.id'),
                    'name' => $item->getProperty('name'),
                    'ssid' => $item->getProperty('ssid'),
                    'frequency' => $item->getProperty('frequency'),
                    'mode' => $item->getProperty('mode'),
                    'band' => $item->getProperty('band'),
                    'channel_width' => $item->getProperty('channel-width'),
                    'security_profile' => $item->getProperty('security-profile'),
                    'disabled' => $item->getProperty('disabled') === 'true',
                ];
            }
            return $interfaces;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getWifiInterfaces failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function getWifiSecurityProfiles(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/interface/wireless/security-profiles/print'));

            $profiles = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $profiles[] = [
                    'id' => $item->getProperty('.id'),
                    'name' => $item->getProperty('name'),
                    'mode' => $item->getProperty('mode'),
                    'authentication_types' => $item->getProperty('authentication-types'),
                    'wpa_pre_shared_key' => $item->getProperty('wpa-pre-shared-key'),
                ];
            }
            return $profiles;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getWifiSecurityProfiles failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function updateWifiSsid(Routeur $routeur, string $interfaceId, string $ssid): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/interface/wireless/set');
            $req->setArgument('numbers', $interfaceId);
            $req->setArgument('ssid', $ssid);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik updateWifiSsid failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function getWifiRegistrations(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/interface/wireless/registration-table/print'));

            $clients = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $clients[] = [
                    'id' => $item->getProperty('.id'),
                    'mac_address' => $item->getProperty('mac-address'),
                    'interface' => $item->getProperty('interface'),
                    'uptime' => $item->getProperty('uptime'),
                    'signal_strength' => $item->getProperty('signal-strength'),
                    'tx_rate' => $item->getProperty('tx-rate'),
                    'rx_rate' => $item->getProperty('rx-rate'),
                ];
            }
            return $clients;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getWifiRegistrations failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    // ===== ROUTES =====

    public function getRoutes(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/route/print'));

            $routes = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $routes[] = [
                    'id'               => $item->getProperty('.id'),
                    'dst_address'      => $item->getProperty('dst-address'),
                    'gateway'          => $item->getProperty('gateway'),
                    'distance'         => $item->getProperty('distance'),
                    'scope'            => $item->getProperty('scope'),
                    'target_scope'     => $item->getProperty('target-scope'),
                    'comment'          => $item->getProperty('comment'),
                    'disabled'         => $item->getProperty('disabled') === 'true',
                    'active'           => $item->getProperty('active') === 'true',
                    'dynamic'          => $item->getProperty('dynamic') === 'true',
                    'static'           => $item->getProperty('static') === 'true',
                    'connect'          => $item->getProperty('connect') === 'true',
                    'gateway_status'   => $item->getProperty('gateway-status'),
                    'routing_mark'     => $item->getProperty('routing-mark'),
                    'pref_src'         => $item->getProperty('pref-src'),
                ];
            }
            return $routes;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getRoutes failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function addRoute(Routeur $routeur, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/route/add');
            $req->setArgument('dst-address', $data['dst_address']);
            $req->setArgument('gateway', $data['gateway']);

            if (!empty($data['distance'])) $req->setArgument('distance', $data['distance']);
            if (!empty($data['comment'])) $req->setArgument('comment', $data['comment']);

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik addRoute failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeRoute(Routeur $routeur, string $routeId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/route/remove');
            $req->setArgument('numbers', $routeId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeRoute failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Modifier une route existante
     */
    public function updateRoute(Routeur $routeur, string $routeId, array $config): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/route/set');
            $req->setArgument('numbers', $routeId);

            if (!empty($config['dst_address'])) {
                $req->setArgument('dst-address', $config['dst_address']);
            }
            if (!empty($config['gateway'])) {
                $req->setArgument('gateway', $config['gateway']);
            }
            if (isset($config['distance'])) {
                $req->setArgument('distance', (string) $config['distance']);
            }
            if (isset($config['comment'])) {
                $req->setArgument('comment', $config['comment']);
            }
            if (!empty($config['routing_table'])) {
                $req->setArgument('routing-table', $config['routing_table']);
            }
            if (!empty($config['pref_src'])) {
                $req->setArgument('pref-src', $config['pref_src']);
            }
            if (!empty($config['check_gateway'])) {
                $req->setArgument('check-gateway', $config['check_gateway']);
            }

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik updateRoute failed', ['routeur_id' => $routeur->id, 'route_id' => $routeId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Activer une route
     */
    public function enableRoute(Routeur $routeur, string $routeId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/route/enable');
            $req->setArgument('numbers', $routeId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik enableRoute failed', ['routeur_id' => $routeur->id, 'route_id' => $routeId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Désactiver une route
     */
    public function disableRoute(Routeur $routeur, string $routeId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/route/disable');
            $req->setArgument('numbers', $routeId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik disableRoute failed', ['routeur_id' => $routeur->id, 'route_id' => $routeId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ===== BANDE PASSANTE (Queue Simple) =====

    public function getQueues(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/queue/simple/print'));

            $queues = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $queues[] = [
                    'id' => $item->getProperty('.id'),
                    'name' => $item->getProperty('name'),
                    'target' => $item->getProperty('target'),
                    'max_limit' => $item->getProperty('max-limit'),
                    'limit_at' => $item->getProperty('limit-at'),
                    'burst_limit' => $item->getProperty('burst-limit'),
                    'burst_time' => $item->getProperty('burst-time'),
                    'comment' => $item->getProperty('comment'),
                    'disabled' => $item->getProperty('disabled') === 'true',
                ];
            }
            return $queues;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getQueues failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function addQueue(Routeur $routeur, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/queue/simple/add');
            $req->setArgument('name', $data['name']);
            $req->setArgument('target', $data['target']);
            $req->setArgument('max-limit', $data['max_limit']); // format: "upload/download" ex: "10M/50M"

            if (!empty($data['comment'])) $req->setArgument('comment', $data['comment']);

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik addQueue failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function updateQueue(Routeur $routeur, string $queueId, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/queue/simple/set');
            $req->setArgument('numbers', $queueId);

            if (!empty($data['name'])) $req->setArgument('name', $data['name']);
            if (!empty($data['target'])) $req->setArgument('target', $data['target']);
            if (!empty($data['max_limit'])) $req->setArgument('max-limit', $data['max_limit']);
            if (!empty($data['comment'])) $req->setArgument('comment', $data['comment']);

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik updateQueue failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeQueue(Routeur $routeur, string $queueId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/queue/simple/remove');
            $req->setArgument('numbers', $queueId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeQueue failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ===== DHCP =====

    public function getDhcpLeases(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/dhcp-server/lease/print'));

            $leases = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $leases[] = [
                    'id' => $item->getProperty('.id'),
                    'address' => $item->getProperty('address'),
                    'mac_address' => $item->getProperty('mac-address'),
                    'hostname' => $item->getProperty('host-name'),
                    'status' => $item->getProperty('status'),
                    'server' => $item->getProperty('server'),
                    'comment' => $item->getProperty('comment'),
                ];
            }
            return $leases;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getDhcpLeases failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function getDhcpServers(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/dhcp-server/print'));

            $servers = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $servers[] = [
                    'id' => $item->getProperty('.id'),
                    'name' => $item->getProperty('name'),
                    'interface' => $item->getProperty('interface'),
                    'lease_time' => $item->getProperty('lease-time'),
                    'address_pool' => $item->getProperty('address-pool'),
                    'disabled' => $item->getProperty('disabled') === 'true',
                ];
            }
            return $servers;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getDhcpServers failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function getDhcpNetworks(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/dhcp-server/network/print'));

            $networks = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $networks[] = [
                    'id' => $item->getProperty('.id'),
                    'address' => $item->getProperty('address'),
                    'gateway' => $item->getProperty('gateway'),
                    'dns_server' => $item->getProperty('dns-server'),
                    'domain' => $item->getProperty('domain'),
                    'comment' => $item->getProperty('comment'),
                ];
            }
            return $networks;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getDhcpNetworks failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function addDhcpServer(Routeur $routeur, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/dhcp-server/add');
            $req->setArgument('name', $data['name']);
            $req->setArgument('interface', $data['interface']);
            $req->setArgument('address-pool', $data['address_pool']);
            if (!empty($data['lease_time'])) $req->setArgument('lease-time', $data['lease_time']);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik addDhcpServer failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function addDhcpNetwork(Routeur $routeur, array $data): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/dhcp-server/network/add');
            $req->setArgument('address', $data['address']);
            $req->setArgument('gateway', $data['gateway']);
            if (!empty($data['dns_server'])) $req->setArgument('dns-server', $data['dns_server']);
            if (!empty($data['domain'])) $req->setArgument('domain', $data['domain']);
            if (!empty($data['comment'])) $req->setArgument('comment', $data['comment']);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik addDhcpNetwork failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeDhcpServer(Routeur $routeur, string $serverId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/dhcp-server/remove');
            $req->setArgument('numbers', $serverId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeDhcpServer failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeDhcpNetwork(Routeur $routeur, string $networkId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/dhcp-server/network/remove');
            $req->setArgument('numbers', $networkId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeDhcpNetwork failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ===== SYSTEM =====

    public function testConnection(Routeur $routeur): bool
    {
        try {
            $client = $this->client($routeur);
            $client->sendSync(new Request('/system/identity/print'));
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getSystemIdentity(Routeur $routeur): ?string
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/system/identity/print'));
            foreach ($resp as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    return $item->getProperty('name');
                }
            }
            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function reboot(Routeur $routeur): bool
    {
        try {
            $client = $this->client($routeur);
            $client->sendSync(new Request('/system/reboot'));
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik reboot failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ===== INTERFACE MANAGEMENT =====

    /**
     * Récupérer les statistiques temps réel d'une interface (RX/TX bytes)
     */
    public function getInterfaceTraffic(Routeur $routeur, string $interfaceName): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/interface/print'));

            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                if ($item->getProperty('name') !== $interfaceName) continue;

                $rxBytes = (int) ($item->getProperty('rx-byte') ?? 0);
                $txBytes = (int) ($item->getProperty('tx-byte') ?? 0);
                $rxPackets = (int) ($item->getProperty('rx-packet') ?? 0);
                $txPackets = (int) ($item->getProperty('tx-packet') ?? 0);
                $rxErrors = (int) ($item->getProperty('rx-error') ?? 0);
                $txErrors = (int) ($item->getProperty('tx-error') ?? 0);
                $running = $item->getProperty('running') === 'true';

                return [
                    'success' => true,
                    'name' => $interfaceName,
                    'running' => $running,
                    'rx_bytes' => $rxBytes,
                    'tx_bytes' => $txBytes,
                    'rx_packets' => $rxPackets,
                    'tx_packets' => $txPackets,
                    'rx_errors' => $rxErrors,
                    'tx_errors' => $txErrors,
                    'rx_mbps' => round($rxBytes / 1048576, 4),
                    'tx_mbps' => round($txBytes / 1048576, 4),
                    'timestamp' => now()->toISOString(),
                ];
            }

            return ['success' => false, 'message' => 'Interface non trouvée', 'name' => $interfaceName];
        } catch (\Throwable $e) {
            Log::error('Mikrotik getInterfaceTraffic failed', ['routeur_id' => $routeur->id, 'interface' => $interfaceName, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage(), 'name' => $interfaceName];
        }
    }

    /**
     * Activer une interface MikroTik
     */
    public function enableInterface(Routeur $routeur, string $interfaceId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/interface/enable');
            $req->setArgument('numbers', $interfaceId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik enableInterface failed', ['routeur_id' => $routeur->id, 'interface_id' => $interfaceId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Désactiver une interface MikroTik
     */
    public function disableInterface(Routeur $routeur, string $interfaceId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/interface/disable');
            $req->setArgument('numbers', $interfaceId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik disableInterface failed', ['routeur_id' => $routeur->id, 'interface_id' => $interfaceId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Renommer une interface MikroTik
     */
    public function renameInterface(Routeur $routeur, string $interfaceId, string $newName): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/interface/set');
            $req->setArgument('numbers', $interfaceId);
            $req->setArgument('name', $newName);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik renameInterface failed', ['routeur_id' => $routeur->id, 'interface_id' => $interfaceId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Configurer MTU et vitesse d'une interface
     */
    public function configureInterface(Routeur $routeur, string $interfaceId, array $config): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/interface/set');
            $req->setArgument('numbers', $interfaceId);

            if (!empty($config['mtu'])) {
                $req->setArgument('mtu', (string) $config['mtu']);
            }
            if (!empty($config['speed'])) {
                // Note: Sur MikroTik, la vitesse est souvent auto ou dépendante du matériel
                // On peut configurer le rate-limit si c'est une interface avec QoS
                $req->setArgument('l2mtu', (string) $config['speed']);
            }
            if (!empty($config['comment'])) {
                $req->setArgument('comment', $config['comment']);
            }

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik configureInterface failed', ['routeur_id' => $routeur->id, 'interface_id' => $interfaceId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Assigner une adresse IP à une interface
     */
    public function setInterfaceIp(Routeur $routeur, string $interfaceName, string $ip, ?string $network = null): bool
    {
        try {
            $client = $this->client($routeur);

            // D'abord, on vérifie si l'interface a déjà une IP dans ce réseau
            $existing = $client->sendSync(new Request('/ip/address/print'));
            $existingId = null;
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    if ($item->getProperty('interface') === $interfaceName) {
                        $existingId = $item->getProperty('.id');
                        break;
                    }
                }
            }

            if ($existingId) {
                // Mettre à jour l'IP existante
                $req = new Request('/ip/address/set');
                $req->setArgument('numbers', $existingId);
                $req->setArgument('address', $ip);
                $req->setArgument('interface', $interfaceName);
                if ($network) {
                    $req->setArgument('network', $network);
                }
                $client->sendSync($req);
            } else {
                // Ajouter une nouvelle IP
                $req = new Request('/ip/address/add');
                $req->setArgument('address', $ip);
                $req->setArgument('interface', $interfaceName);
                if ($network) {
                    $req->setArgument('network', $network);
                }
                $client->sendSync($req);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik setInterfaceIp failed', ['routeur_id' => $routeur->id, 'interface' => $interfaceName, 'ip' => $ip, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Supprimer une adresse IP d'une interface
     */
    public function removeInterfaceIp(Routeur $routeur, string $addressId): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/ip/address/remove');
            $req->setArgument('numbers', $addressId);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeInterfaceIp failed', ['routeur_id' => $routeur->id, 'address_id' => $addressId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obtenir les adresses IP d'une interface
     */
    public function getInterfaceIps(Routeur $routeur, string $interfaceName): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/ip/address/print'));

            $addresses = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                if ($item->getProperty('interface') === $interfaceName) {
                    $addresses[] = [
                        'id' => $item->getProperty('.id'),
                        'address' => $item->getProperty('address'),
                        'network' => $item->getProperty('network'),
                        'interface' => $item->getProperty('interface'),
                        'disabled' => $item->getProperty('disabled') === 'true',
                        'dynamic' => $item->getProperty('dynamic') === 'true',
                    ];
                }
            }
            return $addresses;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getInterfaceIps failed', ['routeur_id' => $routeur->id, 'interface' => $interfaceName, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Obtenir les détails complets d'une interface avec IP
     */
    public function getInterfaceDetails(Routeur $routeur, string $interfaceId): ?array
    {
        try {
            $client = $this->client($routeur);

            // Obtenir les infos de l'interface
            $req = new Request('/interface/print');
            $req->setQuery('?#.id=' . $interfaceId);
            $resp = $client->sendSync($req);

            $interface = null;
            foreach ($resp as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $interface = [
                        'id' => $item->getProperty('.id'),
                        'name' => $item->getProperty('name'),
                        'type' => $item->getProperty('type') ?? 'unknown',
                        'mtu' => $item->getProperty('mtu'),
                        'l2mtu' => $item->getProperty('l2mtu'),
                        'mac_address' => $item->getProperty('mac-address'),
                        'running' => $item->getProperty('running') === 'true',
                        'disabled' => $item->getProperty('disabled') === 'true',
                        'comment' => $item->getProperty('comment'),
                        'rx_byte' => $item->getProperty('rx-byte'),
                        'tx_byte' => $item->getProperty('tx-byte'),
                        'rx_packet' => $item->getProperty('rx-packet'),
                        'tx_packet' => $item->getProperty('tx-packet'),
                        'rx_errors' => $item->getProperty('rx-error'),
                        'tx_errors' => $item->getProperty('tx-error'),
                        'speed' => $item->getProperty('speed'),
                    ];
                    break;
                }
            }

            if ($interface) {
                // Obtenir les IPs associées
                $interface['addresses'] = $this->getInterfaceIps($routeur, $interface['name']);
            }

            return $interface;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getInterfaceDetails failed', ['routeur_id' => $routeur->id, 'interface_id' => $interfaceId, 'error' => $e->getMessage()]);
            return null;
        }
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

    // ==================== WIFI ZONES ====================

    /**
     * Créer ou mettre à jour un profil de sécurité WiFi
     */
    public function setWifiSecurityProfile(Routeur $routeur, string $profileName, string $ssid, ?string $password = null, bool $isolateClients = true): bool
    {
        try {
            $client = $this->client($routeur);

            // Vérifier si le profil existe
            $req = new Request('/interface/wireless/security-profiles/print');
            $req->setQuery('?name=' . $profileName);
            $existing = $client->sendSync($req);
            $existingId = null;
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $existingId = $item->getProperty('.id');
                    break;
                }
            }

            if ($existingId) {
                // Mettre à jour
                $req = new Request('/interface/wireless/security-profiles/set');
                $req->setArgument('numbers', $existingId);
            } else {
                // Créer
                $req = new Request('/interface/wireless/security-profiles/add');
                $req->setArgument('name', $profileName);
            }

            $req->setArgument('mode', 'dynamic-keys');
            $req->setArgument('authentication-types', 'wpa2-psk,wpa3-psk');

            if ($password) {
                $req->setArgument('wpa2-pre-shared-key', $password);
                $req->setArgument('wpa3-pre-shared-key', $password);
            } else {
                $req->setArgument('authentication-types', 'open');
            }

            // Client isolation via datapath
            $req->setArgument('isolate-stations', $isolateClients ? 'yes' : 'no');

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik setWifiSecurityProfile failed', ['routeur_id' => $routeur->id, 'profile' => $profileName, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Créer ou mettre à jour une interface WiFi (Virtual AP)
     */
    public function setWifiInterface(Routeur $routeur, string $interfaceName, string $ssid, string $securityProfile, string $baseInterface, string $frequencyBand = '2.4ghz-g'): ?string
    {
        try {
            $client = $this->client($routeur);

            // Vérifier si l'interface existe
            $req = new Request('/interface/wireless/print');
            $req->setQuery('?name=' . $interfaceName);
            $existing = $client->sendSync($req);
            $existingId = null;
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $existingId = $item->getProperty('.id');
                    break;
                }
            }

            if ($existingId) {
                // Mettre à jour
                $req = new Request('/interface/wireless/set');
                $req->setArgument('numbers', $existingId);
                $req->setArgument('ssid', $ssid);
                $req->setArgument('security-profile', $securityProfile);
                $req->setArgument('master-interface', $baseInterface);
                $req->setArgument('frequency', $frequencyBand === '5ghz-a' ? '5180' : '2412');
                $req->setArgument('disabled', 'no');
                $client->sendSync($req);
                return $existingId;
            } else {
                // Créer
                $req = new Request('/interface/wireless/add');
                $req->setArgument('name', $interfaceName);
                $req->setArgument('mode', 'ap-bridge');
                $req->setArgument('ssid', $ssid);
                $req->setArgument('master-interface', $baseInterface);
                $req->setArgument('security-profile', $securityProfile);
                $req->setArgument('frequency-mode', 'regulatory-domain');
                $req->setArgument('country', 'france');
                $req->setArgument('frequency', $frequencyBand === '5ghz-a' ? '5180' : '2412');
                $req->setArgument('band', $frequencyBand);
                $req->setArgument('channel-width', '20/40mhz-ee');
                $req->setArgument('wireless-protocol', '802.11');
                $req->setArgument('distance', 'indoors');
                $req->setArgument('disabled', 'no');

                $resp = $client->sendSync($req);
                // Récupérer l'ID retourné
                foreach ($resp as $item) {
                    if ($item instanceof \PEAR2\Net\RouterOS\Response && $item->getProperty('ret')) {
                        return $item->getProperty('ret');
                    }
                }
                return null;
            }
        } catch (\Throwable $e) {
            Log::error('Mikrotik setWifiInterface failed', ['routeur_id' => $routeur->id, 'interface' => $interfaceName, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Activer/Désactiver une interface WiFi
     */
    public function toggleWifiInterface(Routeur $routeur, string $interfaceName, bool $enable): bool
    {
        try {
            $client = $this->client($routeur);
            $cmd = $enable ? '/interface/wireless/enable' : '/interface/wireless/disable';
            $req = new Request($cmd);
            $req->setArgument('numbers', $interfaceName);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik toggleWifiInterface failed', ['routeur_id' => $routeur->id, 'interface' => $interfaceName, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Supprimer une interface WiFi
     */
    public function removeWifiInterface(Routeur $routeur, string $interfaceName): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/interface/wireless/remove');
            $req->setArgument('numbers', $interfaceName);
            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeWifiInterface failed', ['routeur_id' => $routeur->id, 'interface' => $interfaceName, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Créer une queue pour la limitation de bande passante
     */
    public function setBandwidthQueue(Routeur $routeur, string $queueName, string $target, int $downMbps, int $upMbps): bool
    {
        try {
            $client = $this->client($routeur);

            // Vérifier si la queue existe
            $req = new Request('/queue/simple/print');
            $req->setQuery('?name=' . $queueName);
            $existing = $client->sendSync($req);
            $existingId = null;
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $existingId = $item->getProperty('.id');
                    break;
                }
            }

            $maxLimit = $upMbps > 0 ? ($upMbps . 'M/' . $downMbps . 'M') : '0/0';

            if ($existingId) {
                // Mettre à jour
                $req = new Request('/queue/simple/set');
                $req->setArgument('numbers', $existingId);
            } else {
                // Créer
                $req = new Request('/queue/simple/add');
                $req->setArgument('name', $queueName);
            }

            $req->setArgument('target', $target);
            $req->setArgument('max-limit', $maxLimit);
            $req->setArgument('queue', 'default-small/default-small');
            $req->setArgument('disabled', ($downMbps === 0 && $upMbps === 0) ? 'yes' : 'no');

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik setBandwidthQueue failed', ['routeur_id' => $routeur->id, 'queue' => $queueName, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Créer un scheduler pour les plages horaires WiFi
     */
    public function setWifiScheduler(Routeur $routeur, string $schedulerName, string $interfaceName, string $startTime, string $stopTime, array $days): bool
    {
        try {
            $client = $this->client($routeur);

            // Supprimer l'ancien scheduler s'il existe
            $req = new Request('/system/scheduler/print');
            $req->setQuery('?name=' . $schedulerName);
            $existing = $client->sendSync($req);
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $delReq = new Request('/system/scheduler/remove');
                    $delReq->setArgument('numbers', $item->getProperty('.id'));
                    $client->sendSync($delReq);
                }
            }

            // Convertir les jours en format MikroTik
            $dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            $mikrotikDays = [];
            foreach ($days as $day) {
                if (isset($dayNames[$day])) {
                    $mikrotikDays[] = $dayNames[$day];
                }
            }
            $daysStr = implode(',', $mikrotikDays);

            // Créer le scheduler pour activation
            $req = new Request('/system/scheduler/add');
            $req->setArgument('name', $schedulerName . '_on');
            $req->setArgument('start-time', $startTime);
            $req->setArgument('interval', '1d');
            $req->setArgument('on-event', "/interface wireless enable \"{$interfaceName}\"");
            if ($daysStr) {
                $req->setArgument('start-date', 'jan/01/2000');
                $req->setArgument('disabled', 'no');
            }
            $client->sendSync($req);

            // Créer le scheduler pour désactivation
            $req = new Request('/system/scheduler/add');
            $req->setArgument('name', $schedulerName . '_off');
            $req->setArgument('start-time', $stopTime);
            $req->setArgument('interval', '1d');
            $req->setArgument('on-event', "/interface wireless disable \"{$interfaceName}\"");
            if ($daysStr) {
                $req->setArgument('start-date', 'jan/01/2000');
                $req->setArgument('disabled', 'no');
            }
            $client->sendSync($req);

            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik setWifiScheduler failed', ['routeur_id' => $routeur->id, 'scheduler' => $schedulerName, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Supprimer les schedulers d'une zone WiFi
     */
    public function removeWifiSchedulers(Routeur $routeur, string $schedulerName): bool
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/system/scheduler/print');
            $req->setQuery('?name=' . $schedulerName . '_on');
            $existing = $client->sendSync($req);
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $delReq = new Request('/system/scheduler/remove');
                    $delReq->setArgument('numbers', $item->getProperty('.id'));
                    $client->sendSync($delReq);
                }
            }

            $req = new Request('/system/scheduler/print');
            $req->setQuery('?name=' . $schedulerName . '_off');
            $existing = $client->sendSync($req);
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $delReq = new Request('/system/scheduler/remove');
                    $delReq->setArgument('numbers', $item->getProperty('.id'));
                    $client->sendSync($delReq);
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik removeWifiSchedulers failed', ['routeur_id' => $routeur->id, 'scheduler' => $schedulerName, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obtenir les clients connectés à une interface WiFi
     */
    public function getWifiClients(Routeur $routeur, string $interfaceName): array
    {
        try {
            $client = $this->client($routeur);
            $req = new Request('/interface/wireless/registration-table/print');
            $req->setQuery('?interface=' . $interfaceName);
            $resp = $client->sendSync($req);

            $clients = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                $clients[] = [
                    'mac_address' => $item->getProperty('mac-address'),
                    'signal_strength' => $item->getProperty('signal-strength'),
                    'tx_rate' => $item->getProperty('tx-rate'),
                    'rx_rate' => $item->getProperty('rx-rate'),
                    'uptime' => $item->getProperty('uptime'),
                    'bytes' => $item->getProperty('bytes'),
                ];
            }
            return $clients;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getWifiClients failed', ['routeur_id' => $routeur->id, 'interface' => $interfaceName, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Créer un VLAN pour une zone WiFi
     */
    public function createWifiVlan(Routeur $routeur, int $vlanId, string $interfaceName, string $vlanName): bool
    {
        try {
            $client = $this->client($routeur);

            // Vérifier si le VLAN existe
            $req = new Request('/interface/vlan/print');
            $req->setQuery('?name=' . $vlanName);
            $existing = $client->sendSync($req);
            $existingId = null;
            foreach ($existing as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $existingId = $item->getProperty('.id');
                    break;
                }
            }

            if ($existingId) {
                // Mettre à jour
                $req = new Request('/interface/vlan/set');
                $req->setArgument('numbers', $existingId);
                $req->setArgument('vlan-id', (string)$vlanId);
                $req->setArgument('interface', $interfaceName);
            } else {
                // Créer
                $req = new Request('/interface/vlan/add');
                $req->setArgument('name', $vlanName);
                $req->setArgument('vlan-id', (string)$vlanId);
                $req->setArgument('interface', $interfaceName);
            }

            $client->sendSync($req);
            return true;
        } catch (\Throwable $e) {
            Log::error('Mikrotik createWifiVlan failed', ['routeur_id' => $routeur->id, 'vlan' => $vlanId, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obtenir les interfaces WiFi disponibles (master)
     */
    public function getAvailableWifiInterfaces(Routeur $routeur): array
    {
        try {
            $client = $this->client($routeur);
            $resp = $client->sendSync(new Request('/interface/wireless/print'));

            $interfaces = [];
            foreach ($resp as $item) {
                if (! $item instanceof \PEAR2\Net\RouterOS\Response) continue;
                // Ne retourner que les interfaces master (pas les virtual AP)
                if (!$item->getProperty('master-interface')) {
                    $interfaces[] = [
                        'id' => $item->getProperty('.id'),
                        'name' => $item->getProperty('name'),
                        'ssid' => $item->getProperty('ssid'),
                        'band' => $item->getProperty('band'),
                        'frequency' => $item->getProperty('frequency'),
                    ];
                }
            }
            return $interfaces;
        } catch (\Throwable $e) {
            Log::error('Mikrotik getAvailableWifiInterfaces failed', ['routeur_id' => $routeur->id, 'error' => $e->getMessage()]);
            return [];
        }
    }
}
