@extends('layouts.app')

@section('title', 'Pare-feu - ' . $routeur->nom)

@section('content')
<div class="p-4 md:p-6 space-y-6 max-w-[1600px] mx-auto">
    
    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 p-5 rounded-2xl bg-gradient-to-r from-slate-800/80 to-slate-900/80 border border-slate-700/50 backdrop-blur-sm">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <i class="fas fa-shield-alt text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Pare-feu <span class="text-indigo-400">{{ $routeur->nom }}</span></h1>
                <p class="text-sm text-slate-400 flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full bg-slate-700 text-xs">{{ $routeur->adresse_ip }}</span>
                    <span>Gestion des règles réseau</span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('routeurs.show', $routeur) }}" class="px-4 py-2 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 text-sm font-medium transition-all">
                <i class="fas fa-info-circle mr-2"></i>Détails
            </a>
            <a href="{{ route('routeurs.index') }}" class="px-4 py-2 rounded-lg bg-slate-700/50 hover:bg-slate-700 text-slate-300 text-sm font-medium transition-all">
                <i class="fas fa-arrow-left mr-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex p-1 rounded-xl bg-slate-800/50 border border-slate-700/50">
            <a href="?tab=filter" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'filter' ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:text-white' }}">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </a>
            <a href="?tab=nat" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'nat' ? 'bg-emerald-500/20 text-emerald-400' : 'text-slate-400 hover:text-white' }}">
                <i class="fas fa-exchange-alt"></i>
                <span>NAT</span>
            </a>
            <a href="?tab=mangle" class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all {{ $tab === 'mangle' ? 'bg-amber-500/20 text-amber-400' : 'text-slate-400 hover:text-white' }}">
                <i class="fas fa-tags"></i>
                <span>Mangle</span>
            </a>
        </div>
        
        @if($tab === 'filter')
            <button onclick="openFilterModal()" class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-medium transition-all flex items-center gap-2 shadow-lg shadow-cyan-500/20">
                <i class="fas fa-plus"></i>Ajouter règle
            </button>
        @elseif($tab === 'nat')
            <button onclick="openNatModal()" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium transition-all flex items-center gap-2 shadow-lg shadow-emerald-500/20">
                <i class="fas fa-plus"></i>Ajouter NAT
            </button>
        @else
            <button onclick="openMangleModal()" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium transition-all flex items-center gap-2 shadow-lg shadow-amber-500/20">
                <i class="fas fa-plus"></i>Ajouter Mangle
            </button>
        @endif
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
            <button onclick="closeFilterModal()" class="w-8 h-8 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
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
            <button onclick="closeFilterModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-medium transition-all">Annuler</button>
            <button onclick="saveFilterRule()" class="px-4 py-2 rounded-lg bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-medium transition-all flex items-center gap-2">
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
            <button onclick="closeNatModal()" class="w-8 h-8 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
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
            <button onclick="closeNatModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-medium transition-all">Annuler</button>
            <button onclick="saveNatRule()" class="px-4 py-2 rounded-lg bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-medium transition-all flex items-center gap-2">
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
            <button onclick="closeMangleModal()" class="w-8 h-8 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-all">
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
            <button onclick="closeMangleModal()" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-300 text-sm font-medium transition-all">Annuler</button>
            <button onclick="saveMangleRule()" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium transition-all flex items-center gap-2">
                <i class="fas fa-check"></i>Enregistrer
            </button>
        </div>
    </div>
</div>

<script>
const routeurId = {{ $routeur->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

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
        <button onclick="this.parentElement.remove()" class="ml-2 text-current hover:opacity-70">
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
    
    try {
        const response = await fetch(`/routeurs/${routeurId}/firewall/filter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message || 'Règle ajoutée avec succès');
            closeFilterModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Erreur lors de l\'enregistrement', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        showToast('Erreur: ' + e.message, 'error');
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
    
    try {
        const response = await fetch(`/routeurs/${routeurId}/firewall/nat`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message || 'Règle NAT ajoutée avec succès');
            closeNatModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Erreur lors de l\'enregistrement', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        showToast('Erreur: ' + e.message, 'error');
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
    
    try {
        const response = await fetch(`/routeurs/${routeurId}/firewall/mangle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
            showToast(result.message || 'Règle Mangle ajoutée avec succès');
            closeMangleModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(result.message || 'Erreur lors de l\'enregistrement', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (e) {
        showToast('Erreur: ' + e.message, 'error');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};
</script>

<style>
    /* ===== Modern Firewall Container ===== */
    .firewall-container {
        padding: 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    /* ===== Header with Glassmorphism ===== */
    .firewall-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(46, 75, 107, 0.5);
        border-radius: 1rem;
        padding: 1.5rem 2rem;
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .header-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .shield-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: white;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .header-text h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #fff;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .routeur-name {
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .header-subtitle {
        color: #8ba9d0;
        margin: 0.5rem 0 0 0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
    }

    .badge {
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .badge-info {
        background: rgba(102, 126, 234, 0.2);
        color: #a5b4fc;
        border: 1px solid rgba(102, 126, 234, 0.3);
    }

    .dot-separator {
        color: #4a5568;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
    }

    .btn-glass {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
        padding: 0.75rem 1.25rem;
        border-radius: 0.75rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-glass:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    .btn-back:hover {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.3);
    }

    /* ===== Modern Tabs ===== */
    .tabs-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .tabs-wrapper {
        display: flex;
        gap: 0.5rem;
        background: rgba(15, 26, 36, 0.5);
        padding: 0.5rem;
        border-radius: 1rem;
        border: 1px solid rgba(46, 75, 107, 0.3);
    }

    .tab-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1.5rem;
        border-radius: 0.75rem;
        text-decoration: none;
        color: #8ba9d0;
        transition: all 0.3s ease;
        position: relative;
    }

    .tab-item:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #fff;
    }

    .tab-item.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .tab-icon {
        width: 36px;
        height: 36px;
        border-radius: 0.625rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .filter-icon { background: rgba(102, 126, 234, 0.2); }
    .nat-icon { background: rgba(16, 185, 129, 0.2); }
    .mangle-icon { background: rgba(245, 158, 11, 0.2); }

    .tab-item.active .tab-icon {
        background: rgba(255, 255, 255, 0.2);
    }

    .tab-content {
        display: flex;
        flex-direction: column;
    }

    .tab-title {
        font-weight: 600;
        font-size: 0.95rem;
    }

    .tab-desc {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    .tab-indicator {
        position: absolute;
        bottom: -0.5rem;
        left: 50%;
        transform: translateX(-50%);
        width: 20px;
        height: 3px;
        background: #fff;
        border-radius: 2px;
    }

    /* ===== Action Buttons ===== */
    .action-area {
        display: flex;
        gap: 0.75rem;
    }

    .btn-action {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.875rem 1.5rem;
        border-radius: 0.75rem;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #fff;
    }

    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .btn-filter {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-nat {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    .btn-mangle {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
    }

    /* ===== Status Message ===== */
    .status-message {
        background: rgba(102, 126, 234, 0.1);
        border: 1px solid rgba(102, 126, 234, 0.3);
        color: #a5b4fc;
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== Content Area ===== */
    .content-area {
        animation: fadeIn 0.4s ease;
    }

    .content-glass {
        background: rgba(15, 26, 36, 0.4);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(46, 75, 107, 0.3);
        border-radius: 1rem;
        padding: 1.5rem;
        min-height: 400px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== Modern Modal ===== */
    .modern-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2000;
        align-items: center;
        justify-content: center;
    }

    .modern-modal.show {
        display: flex;
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
    }

    .modal-container {
        position: relative;
        background: linear-gradient(135deg, #132231 0%, #0f1a24 100%);
        border: 1px solid rgba(46, 75, 107, 0.5);
        border-radius: 1.25rem;
        max-width: 650px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        animation: modalSlideUp 0.4s ease;
    }

    @keyframes modalSlideUp {
        from { opacity: 0; transform: translateY(30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .modal-header-modern {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 2rem;
        border-bottom: 1px solid rgba(46, 75, 107, 0.3);
    }

    .modal-title-group {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
    }

    .filter-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .modal-header-modern h3 {
        margin: 0;
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .btn-close-modern {
        width: 40px;
        height: 40px;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #8ba9d0;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .btn-close-modern:hover {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.3);
        color: #ef4444;
    }

    .modal-body-modern {
        padding: 2rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.25rem;
    }

    .form-field {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .field-label {
        color: #8ba9d0;
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .field-label i {
        color: #667eea;
    }

    .select-wrapper {
        position: relative;
    }

    .modern-select, .modern-input {
        width: 100%;
        padding: 0.875rem 1rem;
        background: rgba(15, 26, 36, 0.8);
        border: 1px solid rgba(46, 75, 107, 0.5);
        border-radius: 0.75rem;
        color: #fff;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        appearance: none;
    }

    .modern-select {
        padding-right: 2.5rem;
        cursor: pointer;
    }

    .select-arrow {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #8ba9d0;
        pointer-events: none;
        font-size: 0.75rem;
    }

    .modern-select:focus, .modern-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(46, 75, 107, 0.5), transparent);
        margin: 1.5rem 0;
    }

    .section-title {
        color: #fff;
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .section-title i {
        color: #667eea;
    }

    .modal-footer-modern {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding: 1.5rem 2rem;
        border-top: 1px solid rgba(46, 75, 107, 0.3);
    }

    .btn-cancel, .btn-save {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.875rem 1.5rem;
        border-radius: 0.75rem;
        border: none;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-cancel {
        background: rgba(255, 255, 255, 0.05);
        color: #8ba9d0;
        border: 1px solid rgba(46, 75, 107, 0.5);
    }

    .btn-cancel:hover {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
        color: #ef4444;
    }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    /* ===== Responsive ===== */
    @media (max-width: 768px) {
        .firewall-header {
            flex-direction: column;
            text-align: center;
        }

        .header-content {
            flex-direction: column;
        }

        .tabs-wrapper {
            flex-wrap: wrap;
            justify-content: center;
        }

        .tab-item {
            padding: 0.75rem 1rem;
        }

        .tab-desc {
            display: none;
        }

        .tabs-container {
            flex-direction: column;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .modal-container {
            width: 95%;
            margin: 1rem;
        }

        .modal-body-modern, .modal-footer-modern {
            padding: 1.25rem;
        }
    }

    /* ===== Legacy Support ===== */
    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid #2e4b6b;
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
</style>
@endsection
