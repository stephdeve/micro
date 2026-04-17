@extends('layouts.app')

@section('title', 'Zones WiFi - ' . $routeur->nom)

@section('content')
<div class="p-6 space-y-6">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/25">
                <i class="fas fa-wifi text-xl text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Zones WiFi</h1>
                <p class="text-sm text-slate-400">{{ $routeur->nom }} &middot; {{ $routeur->adresse_ip }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('routeurs.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-medium transition-all border border-slate-700 inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button onclick="openModal('add')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white text-sm font-medium transition-all shadow-lg shadow-cyan-500/25 inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> Nouvelle Zone
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400">
            <i class="fas fa-check-circle"></i> <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
            <i class="fas fa-exclamation-circle"></i> <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    @if(count($availableInterfaces) > 0)
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-cyan-400 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> Interfaces WiFi disponibles
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($availableInterfaces as $iface)
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-cyan-500/10 border border-cyan-500/20 text-sm">
                        <i class="fas fa-wifi text-cyan-400"></i>
                        <span class="font-medium text-white">{{ $iface['name'] }}</span>
                        <span class="text-slate-400">{{ $iface['ssid'] }} ({{ $iface['band'] ?? '2.4GHz' }})</span>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif($routeur->statut === 'en_ligne')
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400">
            <i class="fas fa-exclamation-triangle"></i> <span class="text-sm">Aucune interface WiFi physique détectée sur ce routeur.</span>
        </div>
    @else
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
            <i class="fas fa-plug"></i> <span class="text-sm">Le routeur est hors ligne. Impossible de détecter les interfaces WiFi.</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($zones as $zone)
            <div class="bg-slate-800/50 border {{ $zone->active ? 'border-cyan-500/30 hover:border-cyan-500/60' : 'border-slate-700 opacity-70' }} rounded-xl overflow-hidden transition-all hover:-translate-y-0.5" id="zone-{{ $zone->id }}">
                <div class="flex items-center justify-between px-4 py-3 bg-slate-900/50 border-b border-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $zone->active ? 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.5)]' : 'bg-rose-400' }}"></span>
                        <h3 class="font-semibold text-white text-sm">{{ $zone->nom }}</h3>
                    </div>
                    <div class="flex items-center gap-1">
                        <button onclick="toggleZone({{ $zone->id }})" class="w-7 h-7 rounded-lg {{ $zone->active ? 'bg-amber-500/10 text-amber-400 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' }} transition-all flex items-center justify-center" title="{{ $zone->active ? 'Désactiver' : 'Activer' }}">
                            <i class="fas fa-power-off text-xs"></i>
                        </button>
                        <button onclick="editZone({{ $zone->id }})" class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-all flex items-center justify-center" title="Modifier">
                            <i class="fas fa-edit text-xs"></i>
                        </button>
                        <button onclick="deleteZone({{ $zone->id }})" class="w-7 h-7 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-all flex items-center justify-center" title="Supprimer">
                            <i class="fas fa-trash text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-wifi text-cyan-400"></i>
                        <span class="text-white font-medium">{{ $zone->ssid }}</span>
                        @if($zone->password)
                            <span class="ml-auto text-xs px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <i class="fas fa-lock"></i> Sécurisé
                            </span>
                        @else
                            <span class="ml-auto text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <i class="fas fa-lock-open"></i> Ouvert
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-2 p-3 bg-slate-900/50 rounded-lg">
                        <div class="flex items-center gap-1.5 text-xs text-slate-300">
                            <i class="fas fa-tachometer-alt text-cyan-400 w-4"></i> {{ $zone->bandwidthFormatted() }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-300">
                            <i class="fas fa-database text-cyan-400 w-4"></i> {{ $zone->quotaFormatted() }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-300">
                            <i class="fas fa-users text-cyan-400 w-4"></i> Max {{ $zone->max_clients }}
                        </div>
                        @if($zone->vlan_id)
                            <div class="flex items-center gap-1.5 text-xs text-slate-300">
                                <i class="fas fa-network-wired text-purple-400 w-4"></i> VLAN {{ $zone->vlan_id }}
                            </div>
                        @endif
                        @if($zone->client_isolation)
                            <div class="flex items-center gap-1.5 text-xs text-slate-300">
                                <i class="fas fa-user-shield text-amber-400 w-4"></i> Isolé
                            </div>
                        @endif
                        @if($zone->scheduleFormatted())
                            <div class="flex items-center gap-1.5 text-xs text-slate-300 col-span-2">
                                <i class="fas fa-clock text-rose-400 w-4"></i> {{ $zone->scheduleFormatted() }}
                            </div>
                        @endif
                    </div>

                    @if(isset($wifiClients[$zone->id]))
                        <div class="border-t border-slate-700 pt-3">
                            <div class="flex items-center justify-between cursor-pointer text-xs text-slate-400 hover:text-white transition-colors" onclick="toggleClients({{ $zone->id }})">
                                <span><i class="fas fa-users mr-1"></i> {{ count($wifiClients[$zone->id]) }} connecté(s)</span>
                                <i class="fas fa-chevron-down" id="chevron-{{ $zone->id }}"></i>
                            </div>
                            <div class="clients-list mt-2 space-y-1" id="clients-{{ $zone->id }}" style="display: none;">
                                @foreach($wifiClients[$zone->id] as $client)
                                    <div class="flex items-center gap-2 px-2 py-1 bg-slate-900/50 rounded text-xs">
                                        <i class="fas fa-mobile-alt text-slate-500"></i>
                                        <span class="font-mono text-cyan-400">{{ $client['mac_address'] }}</span>
                                        <span class="ml-auto {{ signalClass($client['signal_strength'] ?? '') }}">
                                            {{ $client['signal_strength'] ?? 'N/A' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($zone->commentaire)
                        <div class="text-xs text-slate-400 bg-slate-900/50 rounded-lg px-3 py-2">
                            <i class="fas fa-comment mr-1"></i> {{ $zone->commentaire }}
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between px-4 py-2 bg-slate-900/70 border-t border-slate-700 text-xs">
                    <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">{{ $zone->frequency_band === '5ghz-a' ? '5 GHz' : '2.4 GHz' }}</span>
                    <span class="text-slate-500">{{ $zone->wifi_interface_name }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-16 bg-slate-800/30 border-2 border-dashed border-slate-700 rounded-xl">
                <i class="fas fa-wifi text-4xl text-slate-600 mb-4"></i>
                <h3 class="text-lg font-semibold text-white mb-1">Aucune zone WiFi configurée</h3>
                <p class="text-sm text-slate-400 mb-4">Créez votre première zone WiFi pour commencer</p>
                <button onclick="openModal('add')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white text-sm font-medium transition-all shadow-lg shadow-cyan-500/25 inline-flex items-center gap-2">
                    <i class="fas fa-plus"></i> Créer une zone
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Zone WiFi -->
<div id="zoneModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-2">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-2xl w-full max-h-[75vh] md:max-h-[550px] flex flex-col transform transition-all scale-100">
        <!-- Header - fixed -->
        <div class="px-4 py-2 border-b border-slate-700 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/25">
                    <i class="fas fa-wifi text-white"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-base font-bold text-white">Nouvelle Zone WiFi</h3>
                    <p class="text-[10px] text-slate-400">Configuration du réseau sans fil</p>
                </div>
            </div>
            <button onclick="closeModal()" class="w-7 h-7 rounded bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-all flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <!-- Form -->
        <form id="zoneForm" method="POST" action="{{ route('routeurs.wifi-zones.store', $routeur) }}" class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <input type="hidden" id="method" value="POST">
            <input type="hidden" id="zoneId" name="zone_id">

            <!-- Scrollable body -->
            <div class="flex-1 overflow-y-auto p-3 space-y-3">
                <!-- Section 1: Informations générales -->
                <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-info-circle text-blue-400 text-xs"></i>
                        </div>
                        <h4 class="font-semibold text-white text-sm">Informations générales</h4>
                        <span class="ml-auto text-[10px] text-slate-500">* Obligatoire</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label for="nom" class="text-xs font-medium text-slate-300">Nom <span class="text-rose-400">*</span></label>
                            <input type="text" id="nom" name="nom" required
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/20"
                                   placeholder="ex: Direction">
                        </div>
                        <div class="space-y-1">
                            <label for="ssid" class="text-xs font-medium text-slate-300">SSID <span class="text-rose-400">*</span></label>
                            <input type="text" id="ssid" name="ssid" required maxlength="32"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/20"
                                   placeholder="ex: BHT-Direction">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2">
                        <div class="space-y-1">
                            <label for="password" class="text-xs font-medium text-slate-300">Mot de passe</label>
                            <div class="relative">
                                <input type="password" id="password" name="password" minlength="8"
                                       class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/20 pr-8"
                                       placeholder="Vide = ouvert">
                                <button type="button" onclick="togglePassword()" 
                                        class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 rounded hover:bg-slate-700 text-slate-400 hover:text-white transition-colors flex items-center justify-center">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label for="wifi_interface" class="text-xs font-medium text-slate-300">Interface <span class="text-rose-400">*</span></label>
                            <select id="wifi_interface" name="wifi_interface" required
                                    class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/20">
                                <option value="" class="text-slate-500">-- Sélectionnez --</option>
                                @foreach($availableInterfaces as $iface)
                                    <option value="{{ $iface['name'] }}">{{ $iface['name'] }}</option>
                                @endforeach
                                <option value="wlan1">wlan1</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Bande passante -->
                <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-tachometer-alt text-emerald-400 text-xs"></i>
                        </div>
                        <h4 class="font-semibold text-white text-sm">Bande passante</h4>
                        <span class="ml-auto text-[10px] text-slate-500">0 = illimité</span>
                    </div>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="space-y-1">
                            <label for="bandwidth_down" class="text-xs font-medium text-slate-300">Download</label>
                            <input type="number" id="bandwidth_down" name="bandwidth_down" min="0"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20"
                                   placeholder="Mbps">
                        </div>
                        <div class="space-y-1">
                            <label for="bandwidth_up" class="text-xs font-medium text-slate-300">Upload</label>
                            <input type="number" id="bandwidth_up" name="bandwidth_up" min="0"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500/20"
                                   placeholder="Mbps">
                        </div>
                        <div class="space-y-1">
                            <label for="quota_monthly" class="text-xs font-medium text-slate-300">Quota (Mo)</label>
                            <input type="number" id="quota_monthly" name="quota_monthly" min="0"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20"
                                   placeholder="0">
                        </div>
                        <div class="space-y-1">
                            <label for="max_clients" class="text-xs font-medium text-slate-300">Max clients</label>
                            <input type="number" id="max_clients" name="max_clients" value="50" min="1" max="200"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Options avancées -->
                <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded bg-purple-500/20 flex items-center justify-center">
                            <i class="fas fa-cog text-purple-400 text-xs"></i>
                        </div>
                        <h4 class="font-semibold text-white text-sm">Options avancées</h4>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label for="vlan_id" class="text-xs font-medium text-slate-300">VLAN ID</label>
                            <input type="number" id="vlan_id" name="vlan_id" min="1" max="4094"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500/20"
                                   placeholder="ex: 10">
                        </div>
                        <div class="space-y-1">
                            <label for="frequency_band" class="text-xs font-medium text-slate-300">Fréquence</label>
                            <select id="frequency_band" name="frequency_band"
                                    class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/20">
                                <option value="2.4ghz-g">2.4 GHz</option>
                                <option value="5ghz-a">5 GHz</option>
                            </select>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer group mt-2 p-1.5 rounded hover:bg-slate-800/50 transition-colors">
                        <input type="checkbox" id="client_isolation" name="client_isolation" value="1" checked
                               class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-amber-500 focus:ring-amber-500/30">
                        <span class="text-xs font-medium text-slate-300 group-hover:text-white flex items-center gap-1">
                            <i class="fas fa-user-shield text-amber-400"></i> Isolation clients
                        </span>
                    </label>
                </div>

                <!-- Section 4: Plages horaires -->
                <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded bg-rose-500/20 flex items-center justify-center">
                            <i class="fas fa-clock text-rose-400 text-xs"></i>
                        </div>
                        <h4 class="font-semibold text-white text-sm">Horaires</h4>
                        <span class="ml-auto text-[10px] text-slate-500">Facultatif</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label for="schedule_start" class="text-xs font-medium text-slate-300">Début</label>
                            <input type="time" id="schedule_start" name="schedule_start"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20">
                        </div>
                        <div class="space-y-1">
                            <label for="schedule_end" class="text-xs font-medium text-slate-300">Fin</label>
                            <input type="time" id="schedule_end" name="schedule_end"
                                   class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500/20">
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="text-xs font-medium text-slate-300 mb-1 block">Jours</label>
                        <div class="flex flex-wrap gap-1">
                            @foreach(['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'] as $index => $day)
                                <label class="relative cursor-pointer group">
                                    <input type="checkbox" name="schedule_days[]" value="{{ $index }}"
                                           class="peer sr-only">
                                    <span class="px-2 py-1 rounded bg-slate-900 border border-slate-700 text-slate-400 peer-checked:bg-rose-500/20 peer-checked:border-rose-500 peer-checked:text-rose-400 hover:border-slate-500 transition-all block text-[10px] font-medium">
                                        {{ $day }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Section 5: Notes -->
                <div class="bg-slate-800/50 border border-slate-700 rounded-lg p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 rounded bg-slate-500/20 flex items-center justify-center">
                            <i class="fas fa-comment text-slate-400 text-xs"></i>
                        </div>
                        <h4 class="font-semibold text-white text-sm">Notes</h4>
                    </div>
                    <textarea id="commentaire" name="commentaire" rows="1"
                              class="w-full px-3 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm placeholder-slate-500 focus:outline-none focus:border-slate-500 focus:ring-1 focus:ring-slate-500/20 resize-none"
                              placeholder="Description..."></textarea>
                </div>
            </div>

            <!-- Footer - sticky -->
            <div class="px-4 py-2 border-t border-slate-700 bg-slate-900 flex items-center justify-end gap-2 flex-shrink-0">
                <button type="button" onclick="closeModal()" 
                        class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-medium transition-all border border-slate-700">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-3 py-1.5 rounded bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white text-xs font-medium transition-all shadow-lg shadow-cyan-500/25">
                    <i class="fas fa-save mr-1"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .signal-good { color: #34d399; }
    .signal-medium { color: #fbbf24; }
    .signal-bad { color: #f87171; }
</style>

<script>
    function openModal(mode, zoneId = null) {
        const modal = document.getElementById('zoneModal');
        const form = document.getElementById('zoneForm');
        const title = document.getElementById('modalTitle');

        if (mode === 'edit' && zoneId) {
            title.innerHTML = '<i class="fas fa-edit"></i> Modifier Zone WiFi';
            form.action = `/routeurs/{{ $routeur->id }}/wifi-zones/${zoneId}`;
            document.getElementById('method').value = 'PUT';
            // Charger les données de la zone
            fetch(`/routeurs/{{ $routeur->id }}/wifi-zones/${zoneId}/show`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('zoneId').value = data.zone.id;
                    document.getElementById('nom').value = data.zone.nom;
                    document.getElementById('ssid').value = data.zone.ssid;
                    document.getElementById('password').value = '';
                    document.getElementById('bandwidth_down').value = data.zone.bandwidth_down;
                    document.getElementById('bandwidth_up').value = data.zone.bandwidth_up;
                    document.getElementById('quota_monthly').value = data.zone.quota_monthly;
                    document.getElementById('vlan_id').value = data.zone.vlan_id || '';
                    document.getElementById('frequency_band').value = data.zone.frequency_band;
                    document.getElementById('max_clients').value = data.zone.max_clients;
                    document.getElementById('commentaire').value = data.zone.commentaire || '';
                    document.getElementById('schedule_start').value = data.zone.schedule_start || '';
                    document.getElementById('schedule_end').value = data.zone.schedule_end || '';
                    document.getElementById('client_isolation').checked = data.zone.client_isolation;

                    // Sélectionner les jours
                    const dayCheckboxes = document.querySelectorAll('input[name="schedule_days[]"]');
                    dayCheckboxes.forEach(cb => {
                        cb.checked = data.zone.schedule_days && data.zone.schedule_days.includes(parseInt(cb.value));
                    });
                });
        } else {
            title.innerHTML = '<i class="fas fa-wifi"></i> Nouvelle Zone WiFi';
            form.action = '{{ route('routeurs.wifi-zones.store', $routeur) }}';
            form.reset();
            document.getElementById('method').value = 'POST';
            document.getElementById('zoneId').value = '';
            document.getElementById('client_isolation').checked = true;
            document.getElementById('max_clients').value = 50;
            document.getElementById('frequency_band').value = '2.4ghz-g';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        const modal = document.getElementById('zoneModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    // Fermer le modal en cliquant en dehors
    document.getElementById('zoneModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Fermer avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    function togglePassword() {
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
    }

    function toggleClients(zoneId) {
        const list = document.getElementById('clients-' + zoneId);
        const chevron = document.getElementById('chevron-' + zoneId);
        if (list.style.display === 'none') {
            list.style.display = 'block';
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        } else {
            list.style.display = 'none';
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        }
    }

    function toggleZone(zoneId) {
        fetch(`/routeurs/{{ $routeur->id }}/wifi-zones/${zoneId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function editZone(zoneId) {
        openModal('edit', zoneId);
    }

    function deleteZone(zoneId) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette zone WiFi ?')) {
            return;
        }

        fetch(`/routeurs/{{ $routeur->id }}/wifi-zones/${zoneId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('zone-' + zoneId).remove();
            }
        });
    }
</script>

@php
function signalClass($signal) {
    if (!$signal) return 'signal-bad';
    $val = (int) $signal;
    if ($val > -65) return 'signal-good';
    if ($val > -75) return 'signal-medium';
    return 'signal-bad';
}
@endphp
@endsection
