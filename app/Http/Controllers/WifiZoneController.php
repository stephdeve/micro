<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use App\Models\WifiZone;
use App\Services\MikrotikService;
use Illuminate\Http\Request;

class WifiZoneController extends Controller
{
    private MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->middleware('auth');
        $this->mikrotik = $mikrotik;
    }

    /**
     * Liste des zones WiFi d'un routeur
     */
    public function index(Routeur $routeur)
    {
        $zones = $routeur->wifiZones()->orderBy('nom')->get();
        
        // Récupérer les interfaces WiFi disponibles sur le routeur
        $availableInterfaces = [];
        $wifiClients = [];
        
        if ($routeur->statut === 'en_ligne') {
            $availableInterfaces = $this->mikrotik->getAvailableWifiInterfaces($routeur);
            
            // Récupérer les clients pour chaque zone active
            foreach ($zones as $zone) {
                if ($zone->active && $zone->wifi_interface_name) {
                    $wifiClients[$zone->id] = $this->mikrotik->getWifiClients($routeur, $zone->wifi_interface_name);
                }
            }
        }

        return view('reseau.wifi-zones', compact('routeur', 'zones', 'availableInterfaces', 'wifiClients'));
    }

    /**
     * Créer une nouvelle zone WiFi
     */
    public function store(Request $request, Routeur $routeur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'ssid' => 'required|string|max:32',
            'password' => 'nullable|string|min:8|max:63',
            'bandwidth_down' => 'nullable|integer|min:0',
            'bandwidth_up' => 'nullable|integer|min:0',
            'per_user_down' => 'nullable|integer|min:0',
            'per_user_up' => 'nullable|integer|min:0',
            'quota_monthly' => 'nullable|integer|min:0',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'network_address' => 'nullable|string|max:50',
            'gateway' => 'nullable|string|max:50',
            'dhcp_pool_start' => 'nullable|string|max:50',
            'dhcp_pool_end' => 'nullable|string|max:50',
            'schedule_start' => 'nullable|date_format:H:i',
            'schedule_end' => 'nullable|date_format:H:i|after:schedule_start',
            'schedule_days' => 'nullable|array',
            'schedule_days.*' => 'integer|between:0,6',
            'client_isolation' => 'boolean',
            'max_clients' => 'integer|min:1|max:200',
            'frequency_band' => 'in:2.4ghz-g,5ghz-a',
            'wifi_interface' => 'required|string',
            'commentaire' => 'nullable|string',
        ]);

        // Créer la zone dans la base de données
        $zone = new WifiZone([
            'nom' => $validated['nom'],
            'ssid' => $validated['ssid'],
            'password' => $validated['password'],
            'security_profile' => 'zone_' . str_replace(' ', '_', strtolower($validated['nom'])),
            'bandwidth_down' => $validated['bandwidth_down'] ?? 0,
            'bandwidth_up' => $validated['bandwidth_up'] ?? 0,
            'per_user_down' => $validated['per_user_down'] ?? 0,
            'per_user_up' => $validated['per_user_up'] ?? 0,
            'quota_monthly' => $validated['quota_monthly'] ?? 0,
            'vlan_id' => $validated['vlan_id'] ?? null,
            'network_address' => $validated['network_address'] ?? null,
            'gateway' => $validated['gateway'] ?? null,
            'dhcp_pool_start' => $validated['dhcp_pool_start'] ?? null,
            'dhcp_pool_end' => $validated['dhcp_pool_end'] ?? null,
            'schedule_start' => $validated['schedule_start'] ?? null,
            'schedule_end' => $validated['schedule_end'] ?? null,
            'schedule_days' => $validated['schedule_days'] ?? null,
            'client_isolation' => $validated['client_isolation'] ?? true,
            'max_clients' => $validated['max_clients'] ?? 50,
            'frequency_band' => $validated['frequency_band'] ?? '2.4ghz-g',
            'wifi_interface_name' => 'zone_' . preg_replace('/[^a-zA-Z0-9]/', '_', $validated['nom']),
            'active' => true,
            'commentaire' => $validated['commentaire'] ?? null,
        ]);

        $routeur->wifiZones()->save($zone);

        // Configurer sur MikroTik si en ligne
        $syncOk = false;
        if ($routeur->statut === 'en_ligne') {
            try {
                $syncOk = $this->syncZoneToMikrotik($routeur, $zone, $validated['wifi_interface']);
            } catch (\Throwable $e) {
                \Log::error('Sync zone to MikroTik failed: ' . $e->getMessage());
                $syncOk = false;
            }
        }

        $msg = 'Zone WiFi "' . $zone->nom . '" créée avec succès';
        if (!$syncOk && $routeur->statut === 'en_ligne') {
            $msg .= ' (synchronisation MikroTik échouée)';
        } elseif ($routeur->statut !== 'en_ligne') {
            $msg .= ' (routeur hors ligne - sera synchronisé à la prochaine connexion)';
        }

        return redirect()->route('admin-reseau.wifi-zones', $routeur)
            ->with('success', $msg);
    }

    /**
     * Mettre à jour une zone WiFi
     */
    public function update(Request $request, Routeur $routeur, WifiZone $wifiZone)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'ssid' => 'required|string|max:32',
            'password' => 'nullable|string|min:8|max:63',
            'bandwidth_down' => 'nullable|integer|min:0',
            'bandwidth_up' => 'nullable|integer|min:0',
            'per_user_down' => 'nullable|integer|min:0',
            'per_user_up' => 'nullable|integer|min:0',
            'quota_monthly' => 'nullable|integer|min:0',
            'vlan_id' => 'nullable|integer|min:1|max:4094',
            'network_address' => 'nullable|string|max:50',
            'gateway' => 'nullable|string|max:50',
            'dhcp_pool_start' => 'nullable|string|max:50',
            'dhcp_pool_end' => 'nullable|string|max:50',
            'schedule_start' => 'nullable|date_format:H:i',
            'schedule_end' => 'nullable|date_format:H:i|after:schedule_start',
            'schedule_days' => 'nullable|array',
            'schedule_days.*' => 'integer|between:0,6',
            'client_isolation' => 'boolean',
            'max_clients' => 'integer|min:1|max:200',
            'frequency_band' => 'in:2.4ghz-g,5ghz-a',
            'active' => 'boolean',
            'commentaire' => 'nullable|string',
        ]);

        $wifiZone->update([
            'nom' => $validated['nom'],
            'ssid' => $validated['ssid'],
            'password' => $validated['password'] ?? $wifiZone->password,
            'bandwidth_down' => $validated['bandwidth_down'] ?? 0,
            'bandwidth_up' => $validated['bandwidth_up'] ?? 0,
            'per_user_down' => $validated['per_user_down'] ?? 0,
            'per_user_up' => $validated['per_user_up'] ?? 0,
            'quota_monthly' => $validated['quota_monthly'] ?? 0,
            'vlan_id' => $validated['vlan_id'] ?? null,
            'network_address' => $validated['network_address'] ?? null,
            'gateway' => $validated['gateway'] ?? null,
            'dhcp_pool_start' => $validated['dhcp_pool_start'] ?? null,
            'dhcp_pool_end' => $validated['dhcp_pool_end'] ?? null,
            'schedule_start' => $validated['schedule_start'] ?? null,
            'schedule_end' => $validated['schedule_end'] ?? null,
            'schedule_days' => $validated['schedule_days'] ?? null,
            'client_isolation' => $validated['client_isolation'] ?? true,
            'max_clients' => $validated['max_clients'] ?? 50,
            'frequency_band' => $validated['frequency_band'] ?? '2.4ghz-g',
            'active' => $validated['active'] ?? true,
            'commentaire' => $validated['commentaire'] ?? null,
        ]);

        // Resynchroniser avec MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->syncZoneToMikrotik($routeur, $wifiZone);
        }

        return redirect()->route('admin-reseau.wifi-zones', $routeur)
            ->with('success', 'Zone WiFi "' . $wifiZone->nom . '" mise à jour');
    }

    /**
     * Supprimer une zone WiFi
     */
    public function destroy(Routeur $routeur, WifiZone $wifiZone)
    {
        // Supprimer de MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->removeZoneFromMikrotik($routeur, $wifiZone);
        }

        $nom = $wifiZone->nom;
        $wifiZone->delete();

        return redirect()->route('admin-reseau.wifi-zones', $routeur)
            ->with('success', 'Zone WiFi "' . $nom . '" supprimée');
    }

    /**
     * Activer/Désactiver une zone WiFi
     */
    public function toggle(Routeur $routeur, WifiZone $wifiZone)
    {
        $wifiZone->active = !$wifiZone->active;
        $wifiZone->save();

        // Activer/Désactiver sur MikroTik
        if ($routeur->statut === 'en_ligne' && $wifiZone->wifi_interface_name) {
            $this->mikrotik->toggleWifiInterface($routeur, $wifiZone->wifi_interface_name, $wifiZone->active);
        }

        $status = $wifiZone->active ? 'activée' : 'désactivée';
        return redirect()->route('admin-reseau.wifi-zones', $routeur)
            ->with('success', 'Zone WiFi "' . $wifiZone->nom . '" ' . $status);
    }

    /**
     * Synchroniser une zone avec MikroTik
     */
    private function syncZoneToMikrotik(Routeur $routeur, WifiZone $zone, string $baseInterface = 'wlan1'): bool
    {
        // 1. Créer le profil de sécurité
        $this->mikrotik->setWifiSecurityProfile(
            $routeur,
            $zone->security_profile,
            $zone->ssid,
            $zone->password,
            $zone->client_isolation
        );

        // 2. Créer l'interface WiFi (Virtual AP)
        $this->mikrotik->setWifiInterface(
            $routeur,
            $zone->wifi_interface_name,
            $zone->ssid,
            $zone->security_profile,
            $baseInterface,
            $zone->frequency_band
        );

        // 3. Créer la queue pour la limitation de bande passante
        if ($zone->bandwidth_down > 0 || $zone->bandwidth_up > 0) {
            $this->mikrotik->setBandwidthQueue(
                $routeur,
                'zone_' . $zone->id . '_queue',
                $zone->wifi_interface_name,
                $zone->bandwidth_down,
                $zone->bandwidth_up
            );
        }

        // 4. Créer le VLAN si configuré
        if ($zone->vlan_id) {
            $this->mikrotik->createWifiVlan(
                $routeur,
                $zone->vlan_id,
                $zone->wifi_interface_name,
                'vlan_zone_' . $zone->id
            );
        }

        // 5. Configurer les plages horaires si configurées
        if ($zone->schedule_start && $zone->schedule_end) {
            $this->mikrotik->setWifiScheduler(
                $routeur,
                'zone_' . $zone->id,
                $zone->wifi_interface_name,
                $zone->schedule_start,
                $zone->schedule_end,
                $zone->schedule_days ?? []
            );
        } else {
            // Supprimer les schedulers s'ils existent
            $this->mikrotik->removeWifiSchedulers($routeur, 'zone_' . $zone->id);
        }

        // 6. Configurer l'adresse IP de la zone si spécifiée
        if ($zone->network_address && $zone->gateway) {
            // Créer l'adresse IP sur l'interface
            $this->mikrotik->setIpAddress(
                $routeur,
                $zone->network_address,
                $zone->wifi_interface_name
            );

            // Créer le pool DHCP si les plages sont définies
            if ($zone->dhcp_pool_start && $zone->dhcp_pool_end) {
                $poolRange = $zone->dhcp_pool_start . '-' . $zone->dhcp_pool_end;
                $this->mikrotik->setDhcpPool(
                    $routeur,
                    'pool_zone_' . $zone->id,
                    $poolRange
                );

                // Créer le serveur DHCP
                $this->mikrotik->setDhcpServer(
                    $routeur,
                    'dhcp_zone_' . $zone->id,
                    $zone->wifi_interface_name,
                    'pool_zone_' . $zone->id,
                    $zone->network_address,
                    $zone->gateway
                );
            }
        }

        // 7. Configurer la règle firewall time-based pour les horaires
        if ($zone->schedule_start && $zone->schedule_end && $zone->network_address) {
            $this->mikrotik->setTimeFirewallRule(
                $routeur,
                'fw_zone_' . $zone->id . '_schedule',
                $zone->network_address,
                $zone->schedule_start,
                $zone->schedule_end,
                $zone->schedule_days ?? [1, 2, 3, 4, 5] // Lun-Ven par défaut
            );
        } else {
            // Supprimer la règle firewall si elle existe
            $this->mikrotik->removeTimeFirewallRules($routeur, 'fw_zone_' . $zone->id);
        }

        // 8. Configurer la bande passante par utilisateur
        if ($zone->per_user_down > 0 || $zone->per_user_up > 0) {
            // La queue par utilisateur s'applique sur tout le réseau
            $this->mikrotik->setPerUserQueue(
                $routeur,
                'zone_' . $zone->id . '_peruser',
                $zone->network_address ?: $zone->wifi_interface_name,
                $zone->per_user_down,
                $zone->per_user_up
            );
        }

        // 9. Configurer le max clients sur l'interface WiFi
        $this->mikrotik->setWifiMaxClients($routeur, $zone->wifi_interface_name, $zone->max_clients);

        // 10. Activer/Désactiver selon le statut
        $this->mikrotik->toggleWifiInterface($routeur, $zone->wifi_interface_name, $zone->active);

        return true;
    }

    /**
     * Supprimer une zone de MikroTik
     */
    private function removeZoneFromMikrotik(Routeur $routeur, WifiZone $zone): bool
    {
        // Supprimer l'interface WiFi
        if ($zone->wifi_interface_name) {
            $this->mikrotik->removeWifiInterface($routeur, $zone->wifi_interface_name);
        }

        // Supprimer les queues (zone et per-user)
        $this->mikrotik->removeQueueByName($routeur, 'zone_' . $zone->id . '_queue');
        $this->mikrotik->removeQueueByName($routeur, 'zone_' . $zone->id . '_peruser');

        // Supprimer les schedulers
        $this->mikrotik->removeWifiSchedulers($routeur, 'zone_' . $zone->id);

        // Supprimer les règles firewall
        $this->mikrotik->removeTimeFirewallRules($routeur, 'fw_zone_' . $zone->id);

        // Note: On garde l'IP address et DHCP pour éviter de casser d'autres services
        // Ils peuvent être supprimés manuellement sur le routeur si nécessaire

        return true;
    }

    /**
     * Rafraîchir les clients connectés (AJAX)
     */
    public function refreshClients(Routeur $routeur, WifiZone $wifiZone)
    {
        if ($routeur->statut !== 'en_ligne' || !$wifiZone->wifi_interface_name) {
            return response()->json(['clients' => []]);
        }

        $clients = $this->mikrotik->getWifiClients($routeur, $wifiZone->wifi_interface_name);
        
        return response()->json([
            'clients' => $clients,
            'count' => count($clients)
        ]);
    }

    /**
     * Obtenir les détails d'une zone (AJAX)
     */
    public function show(Routeur $routeur, WifiZone $wifiZone)
    {
        return response()->json([
            'zone' => $wifiZone,
            'quota_formatted' => $wifiZone->quotaFormatted(),
            'bandwidth_formatted' => $wifiZone->bandwidthFormatted(),
            'schedule_formatted' => $wifiZone->scheduleFormatted(),
        ]);
    }

    /**
     * Générer les commandes RouterOS pour une zone (AJAX)
     */
    public function getCommands(Routeur $routeur, WifiZone $wifiZone)
    {
        $commands = [];
        $zone = $wifiZone;

        // 1. Interface WiFi
        $commands[] = '# 1. Configuration interface WiFi';
        $commands[] = '/interface wireless set ' . $zone->wifi_interface_name . ' ssid="' . $zone->ssid . '" mode=ap-bridge';
        if ($zone->max_clients > 0) {
            $commands[] = '/interface wireless set ' . $zone->wifi_interface_name . ' max-station-count=' . $zone->max_clients;
        }

        // 2. Profil de sécurité
        $commands[] = '';
        $commands[] = '# 2. Profil de sécurité';
        if ($zone->password) {
            $commands[] = '/interface wireless security-profiles add name=' . $zone->security_profile . ' mode=dynamic-keys authentication-types=wpa2-psk wpa2-pre-shared-key="' . $zone->password . '"';
            $commands[] = '/interface wireless set ' . $zone->wifi_interface_name . ' security-profile=' . $zone->security_profile;
        }

        // 3. IP Address
        if ($zone->network_address && $zone->gateway) {
            $commands[] = '';
            $commands[] = '# 3. Configuration IP';
            $commands[] = '/ip address add address=' . $zone->gateway . ' interface=' . $zone->wifi_interface_name;
        }

        // 4. DHCP
        if ($zone->dhcp_pool_start && $zone->dhcp_pool_end) {
            $commands[] = '';
            $commands[] = '# 4. Configuration DHCP';
            $commands[] = '/ip pool add name=pool_zone_' . $zone->id . ' ranges=' . $zone->dhcp_pool_start . '-' . $zone->dhcp_pool_end;
            $commands[] = '/ip dhcp-server add name=dhcp_zone_' . $zone->id . ' interface=' . $zone->wifi_interface_name . ' address-pool=pool_zone_' . $zone->id;
            $commands[] = '/ip dhcp-server network add address=' . $zone->network_address . ' gateway=' . $zone->gateway;
        }

        // 5. Queues (bande passante)
        if ($zone->bandwidth_down > 0 || $zone->bandwidth_up > 0) {
            $commands[] = '';
            $commands[] = '# 5. Limitation bande passante (zone)';
            $maxLimit = ($zone->bandwidth_up > 0 ? $zone->bandwidth_up . 'M' : '0') . '/' . ($zone->bandwidth_down > 0 ? $zone->bandwidth_down . 'M' : '0');
            $target = $zone->network_address ?: $zone->wifi_interface_name;
            $commands[] = '/queue simple add name=zone_' . $zone->id . '_queue target=' . $target . ' max-limit=' . $maxLimit;
        }

        // 6. Per-user queues
        if ($zone->per_user_down > 0 || $zone->per_user_up > 0) {
            $commands[] = '';
            $commands[] = '# 6. Bande passante par utilisateur';
            $perUserLimit = ($zone->per_user_up > 0 ? $zone->per_user_up . 'M' : '0') . '/' . ($zone->per_user_down > 0 ? $zone->per_user_down . 'M' : '0');
            $target = $zone->network_address ?: $zone->wifi_interface_name;
            $commands[] = '/queue simple add name=zone_' . $zone->id . '_peruser target=' . $target . ' max-limit=' . $perUserLimit;
        }

        // 7. Firewall time-based
        if ($zone->schedule_start && $zone->schedule_end && $zone->network_address) {
            $commands[] = '';
            $commands[] = '# 7. Règle firewall (hors horaires de travail)';
            $dayMap = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
            $days = $zone->schedule_days ?? [1, 2, 3, 4, 5];
            $mikrotikDays = [];
            foreach ($days as $day) {
                if (isset($dayMap[$day])) {
                    $mikrotikDays[] = $dayMap[$day];
                }
            }
            $timeSpec = $zone->schedule_end . '-' . $zone->schedule_start;
            if (!empty($mikrotikDays)) {
                $timeSpec .= ',' . implode(',', $mikrotikDays);
            }
            $commands[] = '/ip firewall filter add chain=forward src-address=' . $zone->network_address . ' time=' . $timeSpec . ' action=drop comment=fw_zone_' . $zone->id . '_schedule';
        }

        return response()->json(['commands' => $commands]);
    }
}
