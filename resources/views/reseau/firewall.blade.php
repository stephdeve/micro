@extends('layouts.app')

@section('title', 'Pare-feu - ' . $routeur->nom)

@section('content')
<div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto">

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Header Moderne avec Stats -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800 via-slate-900 to-slate-950 border border-slate-700/50 backdrop-blur-xl">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/10 via-transparent to-transparent"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom_left,_var(--tw-gradient-stops))] from-cyan-500/5 via-transparent to-transparent"></div>

        <div class="relative p-6 md:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <!-- Titre et Info -->
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cyan-500 via-indigo-500 to-purple-600 flex items-center justify-center shadow-2xl shadow-indigo-500/30 ring-4 ring-slate-950/50">
                            <i class="fas fa-shield-alt text-3xl text-white drop-shadow-lg"></i>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-slate-900 flex items-center justify-center">
                            <i class="fas fa-check text-[10px] text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white tracking-tight">
                            Pare-feu
                            <span class="bg-gradient-to-r from-cyan-400 to-indigo-400 bg-clip-text text-transparent">{{ $routeur->nom }}</span>
                        </h1>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-xs text-slate-300 font-medium">
                                <i class="fas fa-network-wired mr-1.5 text-cyan-400"></i>{{ $routeur->adresse_ip }}
                            </span>
                            <span class="px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-xs text-slate-300">
                                Gestion des règles réseau
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 backdrop-blur-sm hover:bg-slate-800/80 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                                <i class="fas fa-filter text-cyan-400"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Filter</p>
                                <p class="text-lg font-bold text-white">{{ count($groupedFilters['INPUT'] ?? []) + count($groupedFilters['FORWARD'] ?? []) + count($groupedFilters['OUTPUT'] ?? []) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 backdrop-blur-sm hover:bg-slate-800/80 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                <i class="fas fa-exchange-alt text-emerald-400"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">NAT</p>
                                <p class="text-lg font-bold text-white">{{ count($natRules) }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 rounded-xl bg-slate-800/50 border border-slate-700/50 backdrop-blur-sm hover:bg-slate-800/80 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                                <i class="fas fa-tags text-amber-400"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Mangle</p>
                                <p class="text-lg font-bold text-white">{{ count($mangleRules) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Onglets Modernes avec Barre d'outils -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <!-- Tabs -->
        <div class="flex p-1.5 rounded-2xl bg-slate-800/50 border border-slate-700/50 backdrop-blur-sm">
            <a href="?tab=filter" class="group flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ $tab === 'filter' ? 'bg-gradient-to-r from-cyan-500 to-cyan-600 text-white shadow-lg shadow-cyan-500/25' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                <div class="w-8 h-8 rounded-lg {{ $tab === 'filter' ? 'bg-white/20' : 'bg-cyan-500/20' }} flex items-center justify-center transition-all">
                    <i class="fas fa-filter {{ $tab === 'filter' ? 'text-white' : 'text-cyan-400' }}"></i>
                </div>
                <div class="flex flex-col items-start">
                    <span>Filter</span>
                    <span class="text-[10px] {{ $tab === 'filter' ? 'text-cyan-100' : 'text-slate-500' }}">{{ count($groupedFilters['INPUT'] ?? []) + count($groupedFilters['FORWARD'] ?? []) + count($groupedFilters['OUTPUT'] ?? []) }} règles</span>
                </div>
            </a>
            <a href="?tab=nat" class="group flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ $tab === 'nat' ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/25' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                <div class="w-8 h-8 rounded-lg {{ $tab === 'nat' ? 'bg-white/20' : 'bg-emerald-500/20' }} flex items-center justify-center transition-all">
                    <i class="fas fa-exchange-alt {{ $tab === 'nat' ? 'text-white' : 'text-emerald-400' }}"></i>
                </div>
                <div class="flex flex-col items-start">
                    <span>NAT</span>
                    <span class="text-[10px] {{ $tab === 'nat' ? 'text-emerald-100' : 'text-slate-500' }}">{{ count($natRules) }} règles</span>
                </div>
            </a>
            <a href="?tab=mangle" class="group flex items-center gap-2.5 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ $tab === 'mangle' ? 'bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-lg shadow-amber-500/25' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
                <div class="w-8 h-8 rounded-lg {{ $tab === 'mangle' ? 'bg-white/20' : 'bg-amber-500/20' }} flex items-center justify-center transition-all">
                    <i class="fas fa-tags {{ $tab === 'mangle' ? 'text-white' : 'text-amber-400' }}"></i>
                </div>
                <div class="flex flex-col items-start">
                    <span>Mangle</span>
                    <span class="text-[10px] {{ $tab === 'mangle' ? 'text-amber-100' : 'text-slate-500' }}">{{ count($mangleRules) }} règles</span>
                </div>
            </a>
        </div>

        <!-- Barre de recherche et bouton -->
        <div class="flex items-center gap-3 w-full lg:w-auto">
            <div class="relative flex-1 lg:w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" id="searchRules" placeholder="Rechercher une règle..." class="w-full pl-10 pr-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all">
            </div>

            @if($tab === 'filter')
                <button type="button" onclick="openFilterModal()" class="group px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-cyan-600 hover:from-cyan-400 hover:to-cyan-500 text-white text-sm font-semibold transition-all flex items-center gap-2 shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/30 hover:scale-105 active:scale-95">
                    <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
                    <span class="hidden sm:inline">Ajouter règle</span>
                </button>
            @elseif($tab === 'nat')
                <button type="button" onclick="openNatModal()" class="group px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-400 hover:to-emerald-500 text-white text-sm font-semibold transition-all flex items-center gap-2 shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:scale-105 active:scale-95">
                    <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
                    <span class="hidden sm:inline">Ajouter NAT</span>
                </button>
            @else
                <button type="button" onclick="openMangleModal()" class="group px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 text-white text-sm font-semibold transition-all flex items-center gap-2 shadow-lg shadow-amber-500/20 hover:shadow-amber-500/30 hover:scale-105 active:scale-95">
                    <i class="fas fa-plus group-hover:rotate-90 transition-transform"></i>
                    <span class="hidden sm:inline">Ajouter Mangle</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Content -->
    <div class="space-y-4">
        @if($tab === 'filter')
            @include('reseau.firewall-filter')
        @elseif($tab === 'nat')
            @include('reseau.firewall-nat')
        @else
            @include('reseau.firewall-mangle')
        @endif
    </div>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeFilterModal()"></div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                    <i class="fas fa-filter text-cyan-400"></i>
                </div>
                <h3 id="filterModalTitle" class="text-lg font-bold text-white">Ajouter règle Filter</h3>
            </div>
            <button type="button" onclick="closeFilterModal()" class="w-8 h-8 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-5 space-y-4 overflow-y-auto">
            <input type="hidden" id="filterRuleId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Chaîne</label>
                    <select id="filterChain" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500">
                        <option value="INPUT">INPUT (Entrante)</option>
                        <option value="FORWARD" selected>FORWARD (Traversée)</option>
                        <option value="OUTPUT">OUTPUT (Sortante)</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Action</label>
                    <select id="filterAction" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500">
                        <option value="accept">Accepter</option>
                        <option value="drop">Drop (silencieux)</option>
                        <option value="reject">Reject (avec réponse)</option>
                        <option value="log">Log</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Protocole</label>
                    <select id="filterProtocol" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500">
                        <option value="">Tous les protocoles</option>
                        <option value="tcp">TCP</option>
                        <option value="udp">UDP</option>
                        <option value="icmp">ICMP</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Commentaire</label>
                    <input type="text" id="filterComment" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500" placeholder="Description...">
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Source</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Adresse Source</label>
                        <input type="text" id="filterSrcAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500" placeholder="192.168.1.0/24">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Port Source</label>
                        <input type="text" id="filterSrcPort" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500" placeholder="80,443">
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Destination</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Adresse Destination</label>
                        <input type="text" id="filterDstAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500" placeholder="0.0.0.0/0">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Port Destination</label>
                        <input type="text" id="filterDstPort" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500" placeholder="22,80,443">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="px-5 py-4 border-t border-slate-700 flex justify-end gap-2">
            <button type="button" onclick="closeFilterModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-medium transition-all">Annuler</button>
            <button type="button" onclick="saveFilterRule()" class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-check"></i>Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- NAT Modal -->
<div id="natModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeNatModal()"></div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-exchange-alt text-emerald-400"></i>
                </div>
                <h3 id="natModalTitle" class="text-lg font-bold text-white">Ajouter règle NAT</h3>
            </div>
            <button type="button" onclick="closeNatModal()" class="w-8 h-8 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-5 space-y-4 overflow-y-auto">
            <input type="hidden" id="natRuleId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Chaîne</label>
                    <select id="natChain" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500">
                        <option value="srcnat">SRCNAT (Sortant)</option>
                        <option value="dstnat">DSTNAT (Entrant)</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Action</label>
                    <select id="natAction" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500">
                        <option value="accept">Accepter</option>
                        <option value="drop">Drop</option>
                        <option value="masquerade" selected>Masquerade</option>
                        <option value="src-nat">Src-NAT</option>
                        <option value="dst-nat">Dst-NAT</option>
                        <option value="redirect">Redirect</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Protocole</label>
                    <select id="natProtocol" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500">
                        <option value="">Tous</option>
                        <option value="tcp">TCP</option>
                        <option value="udp">UDP</option>
                        <option value="icmp">ICMP</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Commentaire</label>
                    <input type="text" id="natComment" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="Description...">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Interface Sortante</label>
                    <input type="text" id="natOutInterface" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="ether1, wlan1...">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Interface Entrante</label>
                    <input type="text" id="natInInterface" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="ether1, wlan1...">
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Source</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Adresse Source</label>
                        <input type="text" id="natSrcAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="192.168.1.0/24">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Port Source</label>
                        <input type="text" id="natSrcPort" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="80,443">
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Destination</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Adresse Destination</label>
                        <input type="text" id="natDstAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="0.0.0.0/0">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Port Destination</label>
                        <input type="text" id="natDstPort" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="80,443">
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Translation (To)</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">To Addresses</label>
                        <input type="text" id="natToAddresses" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="192.168.88.1">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">To Ports</label>
                        <input type="text" id="natToPorts" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-emerald-500/50 focus:border-emerald-500" placeholder="8080">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="px-5 py-4 border-t border-slate-700 flex justify-end gap-2">
            <button type="button" onclick="closeNatModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-medium transition-all">Annuler</button>
            <button type="button" onclick="saveNatRule()" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-check"></i>Enregistrer
            </button>
        </div>
    </div>
</div>

<!-- Mangle Modal -->
<div id="mangleModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeMangleModal()"></div>
    <div class="bg-slate-800 border border-slate-700 rounded-xl shadow-2xl w-full max-w-2xl relative z-10 max-h-[90vh] flex flex-col">
        <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                    <i class="fas fa-tags text-amber-400"></i>
                </div>
                <h3 id="mangleModalTitle" class="text-lg font-bold text-white">Ajouter règle Mangle</h3>
            </div>
            <button type="button" onclick="closeMangleModal()" class="w-8 h-8 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-5 space-y-4 overflow-y-auto">
            <input type="hidden" id="mangleRuleId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Chaîne</label>
                    <select id="mangleChain" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500">
                        <option value="prerouting">PREROUTING</option>
                        <option value="forward">FORWARD</option>
                        <option value="input">INPUT</option>
                        <option value="output">OUTPUT</option>
                        <option value="postrouting">POSTROUTING</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Action</label>
                    <select id="mangleAction" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500">
                        <option value="accept">Accepter</option>
                        <option value="drop">Drop</option>
                        <option value="mark-packet" selected>Mark Packet</option>
                        <option value="mark-connection">Mark Connection</option>
                        <option value="mark-routing">Mark Routing</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Protocole</label>
                    <select id="mangleProtocol" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500">
                        <option value="">Tous</option>
                        <option value="tcp">TCP</option>
                        <option value="udp">UDP</option>
                        <option value="icmp">ICMP</option>
                    </select>
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Commentaire</label>
                    <input type="text" id="mangleComment" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="Description...">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Interface Entrante</label>
                    <input type="text" id="mangleInInterface" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="ether1...">
                </div>
                
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-400">Interface Sortante</label>
                    <input type="text" id="mangleOutInterface" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="ether1...">
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Source</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Adresse Source</label>
                        <input type="text" id="mangleSrcAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="192.168.1.0/24">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Port Source</label>
                        <input type="text" id="mangleSrcPort" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="80,443">
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Destination</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Adresse Destination</label>
                        <input type="text" id="mangleDstAddress" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="0.0.0.0/0">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Port Destination</label>
                        <input type="text" id="mangleDstPort" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="80,443">
                    </div>
                </div>
            </div>
            
            <div class="border-t border-slate-700 pt-4">
                <h4 class="text-sm font-semibold text-slate-300 mb-3">Marquage</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">New Packet Mark</label>
                        <input type="text" id="mangleNewPacketMark" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="video_traffic">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">New Connection Mark</label>
                        <input type="text" id="mangleNewConnMark" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="heavy_traffic">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">New Routing Mark</label>
                        <input type="text" id="mangleNewRoutingMark" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="via_isp2">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-slate-400">Priority</label>
                        <input type="text" id="manglePriority" class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white text-sm focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500" placeholder="1-8">
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <input type="checkbox" id="manglePassthrough" class="w-4 h-4 rounded border-slate-600 bg-slate-900 text-amber-500">
                <label for="manglePassthrough" class="text-sm text-slate-300">Passthrough (continuer vers les règles suivantes)</label>
            </div>
        </div>
        
        <div class="px-5 py-4 border-t border-slate-700 flex justify-end gap-2">
            <button type="button" onclick="closeMangleModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-medium transition-all">Annuler</button>
            <button type="button" onclick="saveMangleRule()" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-check"></i>Enregistrer
            </button>
        </div>
    </div>
</div>

<script>
const routeurId = {{ $routeur->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
const BASE_URL = '{{ url('admin-reseau') }}';

// Toast System
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    const colors = type === 'success' 
        ? 'bg-emerald-500/20 border-emerald-500/50 text-emerald-400' 
        : 'bg-rose-500/20 border-rose-500/50 text-rose-400';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    toast.className = `flex items-center gap-3 px-4 py-3 rounded-lg border backdrop-blur-sm ${colors} shadow-lg transform transition-all duration-300 translate-x-full`;
    toast.innerHTML = `
        <i class="fas ${icon}"></i>
        <span class="text-sm font-medium">${message}</span>
        <button type="button" onclick="this.parentElement.remove()" class="ml-2 text-current hover:opacity-70">
            <i class="fas fa-times text-xs"></i>
        </button>
    `;
    
    container.appendChild(toast);
    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Filter Modal
window.openFilterModal = function() {
    document.getElementById('filterRuleId').value = '';
    document.getElementById('filterModalTitle').textContent = 'Ajouter règle Filter';
    document.getElementById('filterChain').value = 'FORWARD';
    document.getElementById('filterAction').value = 'accept';
    document.getElementById('filterProtocol').value = '';
    document.getElementById('filterComment').value = '';
    document.getElementById('filterSrcAddress').value = '';
    document.getElementById('filterSrcPort').value = '';
    document.getElementById('filterDstAddress').value = '';
    document.getElementById('filterDstPort').value = '';
    document.getElementById('filterModal').classList.remove('hidden');
    document.getElementById('filterModal').classList.add('flex');
};

window.closeFilterModal = function() {
    document.getElementById('filterModal').classList.add('hidden');
    document.getElementById('filterModal').classList.remove('flex');
};

window.saveFilterRule = async function() {
    const btn = document.querySelector('#filterModal button[onclick="saveFilterRule()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';

    const data = {
        chain: document.getElementById('filterChain').value,
        action: document.getElementById('filterAction').value,
        protocol: document.getElementById('filterProtocol').value,
        comment: document.getElementById('filterComment').value,
        src_address: document.getElementById('filterSrcAddress').value,
        src_port: document.getElementById('filterSrcPort').value,
        dst_address: document.getElementById('filterDstAddress').value,
        dst_port: document.getElementById('filterDstPort').value
    };

    console.log('Saving filter rule:', data);
    console.log('URL:', `${BASE_URL}/routeurs/${routeurId}/firewall/filter`);
    console.log('CSRF Token:', csrfToken ? 'present' : 'missing');

    try {
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/filter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
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
        console.log('Response result:', result);

        if (result.success) {
            alert(result.message || 'Règle ajoutée avec succès');
            closeFilterModal();
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Erreur: ' + (result.message || 'Échec de l\'enregistrement'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error('Exception:', e);
        alert('Erreur: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

// NAT Modal
window.openNatModal = function() {
    document.getElementById('natRuleId').value = '';
    document.getElementById('natModalTitle').textContent = 'Ajouter règle NAT';
    document.getElementById('natChain').value = 'srcnat';
    document.getElementById('natAction').value = 'masquerade';
    document.getElementById('natProtocol').value = '';
    document.getElementById('natComment').value = '';
    document.getElementById('natSrcAddress').value = '';
    document.getElementById('natSrcPort').value = '';
    document.getElementById('natDstAddress').value = '';
    document.getElementById('natDstPort').value = '';
    document.getElementById('natToAddresses').value = '';
    document.getElementById('natToPorts').value = '';
    document.getElementById('natInInterface').value = '';
    document.getElementById('natOutInterface').value = '';
    document.getElementById('natModal').classList.remove('hidden');
    document.getElementById('natModal').classList.add('flex');
};

window.closeNatModal = function() {
    document.getElementById('natModal').classList.add('hidden');
    document.getElementById('natModal').classList.remove('flex');
};

window.saveNatRule = async function() {
    const btn = document.querySelector('#natModal button[onclick="saveNatRule()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';

    const data = {
        chain: document.getElementById('natChain').value,
        action: document.getElementById('natAction').value,
        protocol: document.getElementById('natProtocol').value,
        comment: document.getElementById('natComment').value,
        src_address: document.getElementById('natSrcAddress').value,
        src_port: document.getElementById('natSrcPort').value,
        dst_address: document.getElementById('natDstAddress').value,
        dst_port: document.getElementById('natDstPort').value,
        to_addresses: document.getElementById('natToAddresses').value,
        to_ports: document.getElementById('natToPorts').value,
        in_interface: document.getElementById('natInInterface').value,
        out_interface: document.getElementById('natOutInterface').value
    };

    console.log('Saving NAT rule:', data);

    try {
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/nat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
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
        console.log('Response result:', result);

        if (result.success) {
            alert(result.message || 'Règle NAT ajoutée avec succès');
            closeNatModal();
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Erreur: ' + (result.message || 'Échec de l\'enregistrement'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error('Exception:', e);
        alert('Erreur: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

// Mangle Modal
window.openMangleModal = function() {
    document.getElementById('mangleRuleId').value = '';
    document.getElementById('mangleModalTitle').textContent = 'Ajouter règle Mangle';
    document.getElementById('mangleChain').value = 'prerouting';
    document.getElementById('mangleAction').value = 'mark-packet';
    document.getElementById('mangleProtocol').value = '';
    document.getElementById('mangleComment').value = '';
    document.getElementById('mangleSrcAddress').value = '';
    document.getElementById('mangleSrcPort').value = '';
    document.getElementById('mangleDstAddress').value = '';
    document.getElementById('mangleDstPort').value = '';
    document.getElementById('mangleInInterface').value = '';
    document.getElementById('mangleOutInterface').value = '';
    document.getElementById('mangleNewPacketMark').value = '';
    document.getElementById('mangleNewConnMark').value = '';
    document.getElementById('mangleNewRoutingMark').value = '';
    document.getElementById('manglePriority').value = '';
    document.getElementById('manglePassthrough').checked = false;
    document.getElementById('mangleModal').classList.remove('hidden');
    document.getElementById('mangleModal').classList.add('flex');
};

window.closeMangleModal = function() {
    document.getElementById('mangleModal').classList.add('hidden');
    document.getElementById('mangleModal').classList.remove('flex');
};

window.saveMangleRule = async function() {
    const btn = document.querySelector('#mangleModal button[onclick="saveMangleRule()"]');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';

    const data = {
        chain: document.getElementById('mangleChain').value,
        action: document.getElementById('mangleAction').value,
        protocol: document.getElementById('mangleProtocol').value,
        comment: document.getElementById('mangleComment').value,
        src_address: document.getElementById('mangleSrcAddress').value,
        src_port: document.getElementById('mangleSrcPort').value,
        dst_address: document.getElementById('mangleDstAddress').value,
        dst_port: document.getElementById('mangleDstPort').value,
        in_interface: document.getElementById('mangleInInterface').value,
        out_interface: document.getElementById('mangleOutInterface').value,
        new_packet_mark: document.getElementById('mangleNewPacketMark').value,
        new_conn_mark: document.getElementById('mangleNewConnMark').value,
        new_routing_mark: document.getElementById('mangleNewRoutingMark').value,
        priority: document.getElementById('manglePriority').value,
        passthrough: document.getElementById('manglePassthrough').checked
    };

    console.log('Saving mangle rule:', data);

    try {
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/mangle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
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
        console.log('Response result:', result);

        if (result.success) {
            alert(result.message || 'Règle Mangle ajoutée avec succès');
            closeMangleModal();
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Erreur: ' + (result.message || 'Échec de l\'enregistrement'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        console.error('Exception:', e);
        alert('Erreur: ' + e.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

// Search functionality
document.getElementById('searchRules')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const ruleCards = document.querySelectorAll('[data-id]');

    ruleCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            card.style.display = '';
            card.style.opacity = '1';
        } else {
            card.style.display = 'none';
        }
    });

    // Show/hide empty chain sections
    document.querySelectorAll('.space-y-6 > div, .space-y-4 > div').forEach(section => {
        const visibleCards = section.querySelectorAll('[data-id]:not([style*="display: none"])');
        if (visibleCards.length === 0 && searchTerm !== '') {
            section.style.display = 'none';
        } else {
            section.style.display = '';
        }
    });
});
</script>
@endsection
