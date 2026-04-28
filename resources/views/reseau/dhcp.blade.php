@extends('layouts.app')

@section('title', 'DHCP - ' . $routeur->nom)

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/25">
                <i class="fas fa-server text-xl text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Configuration DHCP</h1>
                <p class="text-sm text-slate-400">{{ $routeur->nom }} &middot; {{ $routeur->adresse_ip }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('routeurs.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-medium transition-all border border-slate-700 inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 border-b border-slate-700 pb-2">
        <button type="button" onclick="showTab('leases')" id="tab-leases" class="px-4 py-2 rounded-lg bg-cyan-500/20 text-cyan-400 text-sm font-medium transition-all">
            <i class="fas fa-list mr-1"></i> Baux DHCP
        </button>
        <button type="button" onclick="showTab('servers')" id="tab-servers" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white text-sm font-medium transition-all">
            <i class="fas fa-server mr-1"></i> Serveurs DHCP
        </button>
        <button type="button" onclick="showTab('networks')" id="tab-networks" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white text-sm font-medium transition-all">
            <i class="fas fa-network-wired mr-1"></i> Réseaux
        </button>
    </div>

    <!-- Tab: Leases -->
    <div id="panel-leases" class="space-y-4">
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <h3 class="font-semibold text-white"><i class="fas fa-list mr-2 text-cyan-400"></i> Baux DHCP</h3>
                    <span class="px-2 py-1 rounded-lg bg-cyan-500/10 text-cyan-400 text-xs font-medium">{{ count($leases ?? []) }} clients</span>
                </div>
                <button type="button" onclick="openLeaseModal()" class="px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white text-xs font-medium transition-all">
                    <i class="fas fa-plus mr-1"></i> Bail statique
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Adresse IP</th>
                            <th class="px-4 py-3 text-left font-medium">MAC</th>
                            <th class="px-4 py-3 text-left font-medium">Hostname</th>
                            <th class="px-4 py-3 text-left font-medium">Statut</th>
                            <th class="px-4 py-3 text-left font-medium">Serveur</th>
                            <th class="px-4 py-3 text-center font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($leases ?? [] as $lease)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3"><code class="text-cyan-400 font-mono">{{ $lease['address'] }}</code></td>
                                <td class="px-4 py-3"><code class="text-slate-300 font-mono text-xs uppercase">{{ $lease['mac_address'] }}</code></td>
                                <td class="px-4 py-3 text-slate-300">{{ $lease['hostname'] ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if(($lease['status'] ?? '') === 'bound')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-xs">Actif</span>
                                    @elseif(($lease['status'] ?? '') === 'waiting')
                                        <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 text-xs">En attente</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-slate-700 text-slate-400 text-xs">{{ $lease['status'] ?? 'inconnu' }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-400">{{ $lease['server'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" onclick="deleteLease('{{ $lease['id'] }}')" class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-all" title="Supprimer le bail">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    <i class="fas fa-list text-2xl mb-2"></i>
                                    <p class="mb-1">Aucun bail DHCP actif</p>
                                    <p class="text-xs text-slate-600 max-w-md mx-auto">
                                        Les baux apparaissent automatiquement quand un client se connecte.<br>
                                        Connectez un appareil ou créez un <strong>bail statique</strong> pour réserver une IP.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Servers -->
    <div id="panel-servers" class="space-y-4 hidden">
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold text-white"><i class="fas fa-server mr-2 text-cyan-400"></i> Serveurs DHCP</h3>
                <button type="button" onclick="openServerModal()" class="px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white text-xs font-medium transition-all">
                    <i class="fas fa-plus mr-1"></i> Ajouter
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Nom</th>
                            <th class="px-4 py-3 text-left font-medium">Interface</th>
                            <th class="px-4 py-3 text-left font-medium">Pool</th>
                            <th class="px-4 py-3 text-left font-medium">Lease Time</th>
                            <th class="px-4 py-3 text-left font-medium">Statut</th>
                            <th class="px-4 py-3 text-center font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($servers ?? [] as $server)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3 text-white font-medium">{{ $server['name'] }}</td>
                                <td class="px-4 py-3 text-cyan-400">{{ $server['interface'] }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $server['address_pool'] }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $server['lease_time'] ?? '1h' }}</td>
                                <td class="px-4 py-3">
                                    @if($server['disabled'] ?? false)
                                        <span class="px-2 py-0.5 rounded-full bg-rose-500/10 text-rose-400 text-xs">Désactivé</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 text-xs">Actif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @if($server['disabled'] ?? false)
                                            <button type="button" onclick="toggleServer('{{ $server['id'] }}', true)" class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition-all" title="Activer">
                                                <i class="fas fa-play text-xs"></i>
                                            </button>
                                        @else
                                            <button type="button" onclick="toggleServer('{{ $server['id'] }}', false)" class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 transition-all" title="Désactiver">
                                                <i class="fas fa-pause text-xs"></i>
                                            </button>
                                        @endif
                                        <button type="button" onclick="deleteServer('{{ $server['id'] }}')" class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-all" title="Supprimer">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                                    <i class="fas fa-server text-2xl mb-2"></i>
                                    <p>Aucun serveur DHCP configuré</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tab: Networks -->
    <div id="panel-networks" class="space-y-4 hidden">
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold text-white"><i class="fas fa-network-wired mr-2 text-cyan-400"></i> Réseaux DHCP</h3>
                <button type="button" onclick="openNetworkModal()" class="px-3 py-1.5 rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white text-xs font-medium transition-all">
                    <i class="fas fa-plus mr-1"></i> Ajouter
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-900/50 text-slate-400">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Adresse</th>
                            <th class="px-4 py-3 text-left font-medium">Passerelle</th>
                            <th class="px-4 py-3 text-left font-medium">DNS</th>
                            <th class="px-4 py-3 text-left font-medium">Domaine</th>
                            <th class="px-4 py-3 text-center font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($networks ?? [] as $network)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="px-4 py-3"><code class="text-cyan-400 font-mono">{{ $network['address'] }}</code></td>
                                <td class="px-4 py-3 text-slate-300">{{ $network['gateway'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $network['dns_server'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $network['domain'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" onclick="deleteNetwork('{{ $network['id'] }}')" class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-all" title="Supprimer">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                                    <i class="fas fa-network-wired text-2xl mb-2"></i>
                                    <p>Aucun réseau DHCP configuré</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Ajouter Serveur DHCP -->
<div id="serverModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeServerModal()"></div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-md relative z-10">
        <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white">Ajouter Serveur DHCP</h3>
            <button type="button" onclick="closeServerModal()" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 space-y-3">
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Nom</label>
                <input type="text" id="serverName" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: dhcp-lan">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Interface</label>
                <select id="serverInterface" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm">
                    <option value="">-- Sélectionnez --</option>
                    @foreach($interfaces ?? [] as $iface)
                        <option value="{{ $iface['name'] }}">{{ $iface['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Address Pool (nom)</label>
                <input type="text" id="serverPool" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: pool-lan">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Plage d'adresses (pool)</label>
                <input type="text" id="serverPoolRange" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: 192.168.1.10-192.168.1.254">
                <p class="text-[10px] text-slate-500">Format: IP début-IP fin (ex: 192.168.1.10-192.168.1.254)</p>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Lease Time</label>
                <input type="text" id="serverLeaseTime" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: 1h">
            </div>
        </div>
        <div class="px-4 py-3 border-t border-slate-700 flex justify-end gap-2">
            <button type="button" onclick="closeServerModal()" class="px-3 py-1.5 rounded bg-slate-700 text-slate-300 text-sm">Annuler</button>
            <button type="button" onclick="saveServer()" class="px-3 py-1.5 rounded bg-cyan-500 hover:bg-cyan-600 text-white text-sm">Créer</button>
        </div>
    </div>
</div>

<!-- Modal: Ajouter Réseau DHCP -->
<div id="networkModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeNetworkModal()"></div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-md relative z-10">
        <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white">Ajouter Réseau DHCP</h3>
            <button type="button" onclick="closeNetworkModal()" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 space-y-3">
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Adresse (CIDR)</label>
                <input type="text" id="networkAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: 192.168.1.0/24">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Passerelle</label>
                <input type="text" id="networkGateway" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: 192.168.1.1">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">DNS Server</label>
                <input type="text" id="networkDns" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: 8.8.8.8,8.8.4.4">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Domaine</label>
                <input type="text" id="networkDomain" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: local.lan">
            </div>
        </div>
        <div class="px-4 py-3 border-t border-slate-700 flex justify-end gap-2">
            <button type="button" onclick="closeNetworkModal()" class="px-3 py-1.5 rounded bg-slate-700 text-slate-300 text-sm">Annuler</button>
            <button type="button" onclick="saveNetwork()" class="px-3 py-1.5 rounded bg-cyan-500 hover:bg-cyan-600 text-white text-sm">Créer</button>
        </div>
    </div>
</div>

<!-- Modal: Ajouter Bail Statique -->
<div id="leaseModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeLeaseModal()"></div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-md relative z-10">
        <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white">Ajouter Bail Statique</h3>
            <button type="button" onclick="closeLeaseModal()" class="text-slate-400 hover:text-white">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-4 space-y-3">
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Adresse IP</label>
                <input type="text" id="leaseAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: 192.168.1.100">
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Adresse MAC</label>
                <input type="text" id="leaseMac" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: AA:BB:CC:DD:EE:FF">
                <p class="text-[10px] text-slate-500">Format: AA:BB:CC:DD:EE:FF ou AA-BB-CC-DD-EE-FF</p>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Serveur DHCP</label>
                <select id="leaseServer" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm">
                    <option value="">-- Sélectionnez --</option>
                    @foreach($servers ?? [] as $srv)
                        <option value="{{ $srv['name'] }}">{{ $srv['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-xs font-medium text-slate-300">Commentaire (optionnel)</label>
                <input type="text" id="leaseComment" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm" placeholder="ex: PC-Bureau">
            </div>
        </div>
        <div class="px-4 py-3 border-t border-slate-700 flex justify-end gap-2">
            <button type="button" onclick="closeLeaseModal()" class="px-3 py-1.5 rounded bg-slate-700 text-slate-300 text-sm">Annuler</button>
            <button type="button" onclick="saveLease()" class="px-3 py-1.5 rounded bg-cyan-500 hover:bg-cyan-600 text-white text-sm">Créer</button>
        </div>
    </div>
</div>

<script>
const routeurId = {{ $routeur->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const BASE_URL = '{{ url('admin-reseau') }}';

if (!csrfToken) {
    console.error('CSRF Token not found!');
    alert('Erreur: Token CSRF manquant. Rechargez la page.');
}

function showTab(tab) {
    // Hide all panels
    document.querySelectorAll('[id^="panel-"]').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('[id^="tab-"]').forEach(el => {
        el.classList.remove('bg-cyan-500/20', 'text-cyan-400');
        el.classList.add('bg-slate-800', 'text-slate-400');
    });
    
    // Show selected
    document.getElementById(`panel-${tab}`).classList.remove('hidden');
    document.getElementById(`tab-${tab}`).classList.remove('bg-slate-800', 'text-slate-400');
    document.getElementById(`tab-${tab}`).classList.add('bg-cyan-500/20', 'text-cyan-400');
}

// Server Modal
function openServerModal() {
    document.getElementById('serverModal').classList.remove('hidden');
    document.getElementById('serverModal').classList.add('flex');
}
function closeServerModal() {
    document.getElementById('serverModal').classList.add('hidden');
    document.getElementById('serverModal').classList.remove('flex');
}

async function saveServer() {
    const data = {
        name: document.getElementById('serverName').value.trim(),
        interface: document.getElementById('serverInterface').value,
        address_pool: document.getElementById('serverPool').value.trim(),
        pool_range: document.getElementById('serverPoolRange').value.trim(),
        lease_time: document.getElementById('serverLeaseTime').value.trim() || '1h'
    };

    console.log('Saving server with data:', data);

    if (!data.name || !data.interface || !data.address_pool || !data.pool_range) {
        alert('Veuillez remplir tous les champs obligatoires (nom, interface, pool, plage)');
        return;
    }
    
    try {
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/dhcp/servers`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }
        
        const result = await response.json();
        console.log('Response data:', result);
        
        if (result.success) {
            alert('Serveur DHCP créé avec succès');
            location.reload();
        } else {
            alert('Erreur: ' + (result.message || 'Échec de la création'));
        }
    } catch (e) {
        console.error('Exception:', e);
        alert('Erreur: ' + e.message);
    }
}

async function toggleServer(id, enable) {
    const action = enable ? 'activer' : 'désactiver';
    if (!confirm(`${action.charAt(0).toUpperCase() + action.slice(1)} ce serveur DHCP ?`)) return;

    console.log(`Toggling server ${id} to ${enable ? 'enabled' : 'disabled'}`);

    // Encoder l'ID car les IDs MikroTik contiennent souvent des * ou autres caractères spéciaux
    const encodedId = encodeURIComponent(id);

    try {
        const url = `${BASE_URL}/routeurs/${routeurId}/dhcp/servers/${encodedId}/${enable ? 'enable' : 'disable'}`;
        console.log('Fetching:', url);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }

        const result = await response.json();
        console.log('Response result:', result);

        if (result.success) {
            alert(result.message || 'Succès !');
            location.reload();
        } else {
            alert('Erreur: ' + (result.message || 'Échec'));
        }
    } catch (e) {
        console.error('Toggle server error:', e);
        alert('Erreur: ' + e.message);
    }
}

async function deleteServer(id) {
    if (!confirm('Supprimer ce serveur DHCP ?')) return;
    try {
        const encodedId = encodeURIComponent(id);
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/dhcp/servers/${encodedId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }
        
        const result = await response.json();
        if (result.success) location.reload();
        else alert('Erreur: ' + (result.message || 'Échec de la suppression'));
    } catch (e) {
        console.error('Delete server error:', e);
        alert('Erreur: ' + e.message);
    }
}

// Network Modal
function openNetworkModal() {
    document.getElementById('networkModal').classList.remove('hidden');
    document.getElementById('networkModal').classList.add('flex');
}
function closeNetworkModal() {
    document.getElementById('networkModal').classList.add('hidden');
    document.getElementById('networkModal').classList.remove('flex');
}

async function saveNetwork() {
    const data = {
        address: document.getElementById('networkAddress').value.trim(),
        gateway: document.getElementById('networkGateway').value.trim(),
        dns_server: document.getElementById('networkDns').value.trim(),
        domain: document.getElementById('networkDomain').value.trim()
    };
    
    console.log('Saving network with data:', data);
    
    if (!data.address || !data.gateway) {
        alert('Veuillez remplir l\'adresse et la passerelle');
        return;
    }
    
    try {
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/dhcp/networks`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }
        
        const result = await response.json();
        console.log('Response data:', result);
        
        if (result.success) {
            alert('Réseau DHCP créé avec succès');
            location.reload();
        } else {
            alert('Erreur: ' + (result.message || 'Échec de la création'));
        }
    } catch (e) {
        console.error('Exception:', e);
        alert('Erreur: ' + e.message);
    }
}

async function deleteNetwork(id) {
    if (!confirm('Supprimer ce réseau DHCP ?')) return;
    try {
        const encodedId = encodeURIComponent(id);
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/dhcp/networks/${encodedId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }
        
        const result = await response.json();
        if (result.success) location.reload();
        else alert('Erreur: ' + (result.message || 'Échec de la suppression'));
    } catch (e) {
        console.error('Delete network error:', e);
        alert('Erreur: ' + e.message);
    }
}

// Lease Modal (Baux statiques)
function openLeaseModal() {
    document.getElementById('leaseModal').classList.remove('hidden');
    document.getElementById('leaseModal').classList.add('flex');
}
function closeLeaseModal() {
    document.getElementById('leaseModal').classList.add('hidden');
    document.getElementById('leaseModal').classList.remove('flex');
}

async function saveLease() {
    const data = {
        address: document.getElementById('leaseAddress').value.trim(),
        mac_address: document.getElementById('leaseMac').value.trim(),
        server: document.getElementById('leaseServer').value,
        comment: document.getElementById('leaseComment').value.trim()
    };

    console.log('Saving lease with data:', data);

    // Validation MAC
    const macPattern = /^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/;
    if (!macPattern.test(data.mac_address)) {
        alert('Format MAC invalide. Utilisez AA:BB:CC:DD:EE:FF ou AA-BB-CC-DD-EE-FF');
        return;
    }

    if (!data.address || !data.server) {
        alert('Veuillez remplir l\'adresse IP et sélectionner un serveur DHCP');
        return;
    }

    try {
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/dhcp/leases`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        console.log('Response status:', response.status);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }

        const result = await response.json();
        console.log('Response data:', result);

        if (result.success) {
            alert('Bail statique créé avec succès');
            location.reload();
        } else {
            alert('Erreur: ' + (result.message || 'Échec de la création'));
        }
    } catch (e) {
        console.error('Exception:', e);
        alert('Erreur: ' + e.message);
    }
}

async function deleteLease(id) {
    if (!confirm('Supprimer ce bail DHCP ?')) return;
    try {
        const encodedId = encodeURIComponent(id);
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/dhcp/leases/${encodedId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
        }

        const result = await response.json();
        if (result.success) location.reload();
        else alert('Erreur: ' + (result.message || 'Échec de la suppression'));
    } catch (e) {
        console.error('Delete lease error:', e);
        alert('Erreur: ' + e.message);
    }
}
</script>
@endsection
