@extends('layouts.app')

@section('title', 'Interfaces - Infrastructure Réseau')

@php
$typeIcons = [
    'ethernet' => 'ethernet',
    'wifi' => 'wifi',
    'bridge' => 'project-diagram',
    'vlan' => 'sitemap'
];
$typeColors = [
    'ethernet' => 'cyan',
    'wifi' => 'emerald',
    'bridge' => 'amber',
    'vlan' => 'purple'
];
$statusColors = [
    'actif' => 'emerald',
    'inactif' => 'rose',
    'erreur' => 'orange'
];

// Prepare interfaces data for Three.js
$colorMapHex = [
    'cyan' => 0x06b6d4,
    'emerald' => 0x10b981,
    'amber' => 0xffaa33,
    'purple' => 0xa855f7
];
$interfacesData = [];
foreach($interfaces as $interface) {
    $colorKey = $typeColors[$interface->type] ?? 'cyan';
    $interfacesData[] = [
        'name' => $interface->nom,
        'type' => $interface->type,
        'color' => $colorMapHex[$colorKey],
        'status' => $interface->statut
    ];
}
@endphp

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4" x-data="{ show3D: true, viewMode: 'grid' }">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                <i class="fas fa-ethernet mr-3"></i>Interfaces Réseau
            </h1>
            <p class="text-slate-400 mt-1">Gestion des interfaces physiques et virtuelles</p>
        </div>
        
        <div class="flex items-center gap-3">
            <!-- Toggle 3D/2D -->
            <button @click="show3D = !show3D" 
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="fas fa-cube" :class="show3D ? 'text-cyan-400' : 'text-slate-400'"></i>
                <span class="text-sm" x-text="show3D ? '3D' : '2D'"></span>
            </button>
            
            <!-- Toggle View Mode -->
            <div class="flex bg-slate-800 rounded-xl p-1 border border-slate-700">
                <button @click="viewMode = 'grid'" 
                        :class="viewMode === 'grid' ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg text-sm transition">
                    <i class="fas fa-th-large"></i>
                </button>
                <button @click="viewMode = 'list'" 
                        :class="viewMode === 'list' ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg text-sm transition">
                    <i class="fas fa-list"></i>
                </button>
            </div>
            
            <button onclick="openModal('add')" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center gap-2 shadow-lg shadow-cyan-500/25">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Nouvelle interface</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-ethernet text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Total</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['totales'] ?? 0) }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">Sur {{ $stats['routeurs'] ?? 0 }} routeurs</div>
            </div>
        </div>

        <!-- Actives -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-circle text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">Actives</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['actives'] ?? 0) }}</div>
                <div class="text-emerald-400/70 text-sm mt-1">+{{ $stats['nouvelles'] ?? 0 }} aujourd'hui</div>
            </div>
        </div>

        <!-- Debit -->
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tachometer-alt text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Débit</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['debit_total'] ?? 0, 1) }}</div>
                <div class="text-amber-400/70 text-sm mt-1">Mbps total</div>
            </div>
        </div>

        <!-- Erreurs -->
        <div class="bg-gradient-to-br from-rose-500/10 to-rose-600/5 border border-rose-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-rose-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-rose-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-rose-400 text-xl"></i>
                    </div>
                    <span class="text-rose-400 text-sm font-medium">Erreurs</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['erreurs'] ?? 0) }}</div>
                <div class="text-rose-400/70 text-sm mt-1">Paquets perdus</div>
            </div>
        </div>
    </div>

    <!-- 3D Network Visualization -->
    <div x-show="show3D" x-transition class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-cube text-cyan-400"></i>
                Topologie des interfaces 3D
            </h3>
            <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
        </div>
        <div id="interfaces3D" class="h-72 w-full"></div>
    </div>

    <!-- Filters -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-4 mb-6">
        <form method="GET" action="{{ route('interfaces.index') }}" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Rechercher par nom, IP, MAC..."
                       class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition">
            </div>
            
            <div class="flex gap-3">
                <select name="routeur_id" onchange="this.form.submit()" 
                        class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50 min-w-[180px]">
                    <option value="">Tous les routeurs</option>
                    @foreach($routeurs as $routeur)
                        <option value="{{ $routeur->id }}" {{ request('routeur_id') == $routeur->id ? 'selected' : '' }}>{{ $routeur->nom }}</option>
                    @endforeach
                </select>
                
                <select name="type" onchange="this.form.submit()" 
                        class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50 min-w-[150px]">
                    <option value="">Tous les types</option>
                    <option value="ethernet" {{ request('type') == 'ethernet' ? 'selected' : '' }}>Ethernet</option>
                    <option value="wifi" {{ request('type') == 'wifi' ? 'selected' : '' }}>WiFi</option>
                    <option value="bridge" {{ request('type') == 'bridge' ? 'selected' : '' }}>Bridge</option>
                    <option value="vlan" {{ request('type') == 'vlan' ? 'selected' : '' }}>VLAN</option>
                </select>
                
                <select name="statut" onchange="this.form.submit()" 
                        class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50 min-w-[150px]">
                    <option value="">Tous les statuts</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="erreur" {{ request('statut') == 'erreur' ? 'selected' : '' }}>Erreur</option>
                </select>
                
                @if(request()->anyFilled(['search', 'routeur_id', 'type', 'statut']))
                    <a href="{{ route('interfaces.index') }}" class="px-4 py-3 bg-slate-700 hover:bg-slate-600 rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid View -->
    <div x-show="viewMode === 'grid'" x-transition class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
        @forelse($interfaces as $interface)
        @php
            $typeIcon = $typeIcons[$interface->type] ?? 'network-wired';
            $typeColor = $typeColors[$interface->type] ?? 'cyan';
            $statusColor = $statusColors[$interface->statut] ?? 'slate';
        @endphp
        <div class="bg-slate-800/50 border border-slate-700 hover:border-{{ $typeColor }}-500/50 rounded-2xl p-5 transition group relative overflow-hidden">
            <!-- Type glow -->
            <div class="absolute top-0 right-0 w-40 h-40 bg-{{ $typeColor }}-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl group-hover:bg-{{ $typeColor }}-500/20 transition"></div>
            
            <div class="relative">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-{{ $typeColor }}-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-{{ $typeIcon }} text-{{ $typeColor }}-400 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white">{{ $interface->nom }}</h4>
                            <span class="text-xs text-{{ $statusColor }}-400 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-{{ $statusColor }}-400 animate-pulse"></span>
                                {{ ucfirst($interface->statut) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="editInterface({{ $interface->id }})" class="p-2 hover:bg-slate-700 rounded-lg transition text-slate-400 hover:text-white" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteInterface({{ $interface->id }})" class="p-2 hover:bg-rose-500/20 rounded-lg transition text-slate-400 hover:text-rose-400" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Routeur</span>
                        <span class="text-white">{{ $interface->routeur->nom ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Type</span>
                        <span class="text-{{ $typeColor }}-400">{{ ucfirst($interface->type) }}{{ $interface->bande ? ' ('.$interface->bande.')' : '' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">MAC</span>
                        <span class="text-cyan-400 font-mono text-xs">{{ $interface->adresse_mac ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Débit</span>
                        <span class="text-white">{{ number_format($interface->debit_entrant ?? 0, 1) }} / {{ number_format($interface->debit_sortant ?? 0, 1) }} Mbps</span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('interfaces.show', $interface) }}" class="flex-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm text-center transition">
                        <i class="fas fa-eye mr-1"></i> Détails
                    </a>
                    <a href="{{ route('interfaces.graph', $interface) }}" class="px-3 py-2 bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 rounded-lg text-sm transition" title="Graphiques">
                        <i class="fas fa-chart-line"></i>
                    </a>
                    <button onclick="toggleInterface({{ $interface->id }})" class="px-3 py-2 {{ $interface->statut == 'actif' ? 'bg-rose-500/20 hover:bg-rose-500/30 text-rose-400' : 'bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400' }} rounded-lg text-sm transition" title="{{ $interface->statut == 'actif' ? 'Désactiver' : 'Activer' }}">
                        <i class="fas fa-power-off"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16">
            <div class="w-20 h-20 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-network-wired text-slate-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Aucune interface trouvée</h3>
            <p class="text-slate-400 mb-6">Commencez par ajouter une nouvelle interface</p>
            <button onclick="openModal('add')" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl font-medium transition">
                <i class="fas fa-plus mr-2"></i>Ajouter une interface
            </button>
        </div>
        @endforelse
    </div>

    <!-- List View -->
    <div x-show="viewMode === 'list'" x-transition class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Interface</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Routeur</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Type</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Adresse MAC</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Statut</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Débit (Rx/Tx)</th>
                        <th class="text-center px-6 py-4 text-sm font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($interfaces as $interface)
                    @php
                        $typeIcon = $typeIcons[$interface->type] ?? 'network-wired';
                        $typeColor = $typeColors[$interface->type] ?? 'cyan';
                        $statusColor = $statusColors[$interface->statut] ?? 'slate';
                    @endphp
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-{{ $typeColor }}-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-{{ $typeIcon }} text-{{ $typeColor }}-400"></i>
                                </div>
                                <span class="font-medium text-white">{{ $interface->nom }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ $interface->routeur->nom ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-{{ $typeColor }}-400">{{ ucfirst($interface->type) }}{{ $interface->bande ? ' ('.$interface->bande.')' : '' }}</td>
                        <td class="px-6 py-4 text-cyan-400 font-mono text-sm">{{ $interface->adresse_mac ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-{{ $statusColor }}-500/20 text-{{ $statusColor }}-400 rounded-full text-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $statusColor }}-400 animate-pulse"></span>
                                {{ ucfirst($interface->statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-300">{{ number_format($interface->debit_entrant ?? 0, 1) }} / {{ number_format($interface->debit_sortant ?? 0, 1) }} Mbps</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('interfaces.show', $interface) }}" class="p-2 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-white transition" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="editInterface({{ $interface->id }})" class="p-2 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-white transition" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="{{ route('interfaces.graph', $interface) }}" class="p-2 hover:bg-amber-500/20 rounded-lg text-amber-400 transition" title="Graphiques">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                                <button onclick="toggleInterface({{ $interface->id }})" class="p-2 hover:{{ $interface->statut == 'actif' ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400' }} rounded-lg text-slate-400 transition" title="{{ $interface->statut == 'actif' ? 'Désactiver' : 'Activer' }}">
                                    <i class="fas fa-power-off"></i>
                                </button>
                                <button onclick="deleteInterface({{ $interface->id }})" class="p-2 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-network-wired text-slate-600 text-2xl"></i>
                            </div>
                            <p class="text-slate-400">Aucune interface trouvée</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($interfaces->hasPages())
    <div class="flex items-center justify-between">
        <div class="text-sm text-slate-400">
            Page {{ $interfaces->currentPage() }} sur {{ $interfaces->lastPage() }} • {{ $interfaces->total() }} interface{{ $interfaces->total() > 1 ? 's' : '' }}
        </div>
        <div class="flex gap-2">
            @if($interfaces->onFirstPage())
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-600 cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $interfaces->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif
            
            @php
                $start = max(1, $interfaces->currentPage() - 2);
                $end = min($interfaces->lastPage(), $interfaces->currentPage() + 2);
            @endphp
            
            @if($start > 1)
                <a href="{{ $interfaces->url(1) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition text-sm">1</a>
                @if($start > 2)
                    <span class="w-10 h-10 flex items-center justify-center text-slate-600">...</span>
                @endif
            @endif
            
            @for($i = $start; $i <= $end; $i++)
                @if($i == $interfaces->currentPage())
                    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-400 font-medium">{{ $i }}</span>
                @else
                    <a href="{{ $interfaces->url($i) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition text-sm">{{ $i }}</a>
                @endif
            @endfor
            
            @if($end < $interfaces->lastPage())
                @if($end < $interfaces->lastPage() - 1)
                    <span class="w-10 h-10 flex items-center justify-center text-slate-600">...</span>
                @endif
                <a href="{{ $interfaces->url($interfaces->lastPage()) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition text-sm">{{ $interfaces->lastPage() }}</a>
            @endif
            
            @if($interfaces->hasMorePages())
                <a href="{{ $interfaces->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-600 cursor-not-allowed">
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif

</div>

<!-- Three.js for 3D Interface Network -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function() {
    const container = document.getElementById('interfaces3D');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1e293b);
    
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.z = 12;
    camera.position.y = 3;
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 2);
    scene.add(ambientLight);
    
    const pointLight = new THREE.PointLight(0x00d4ff, 2, 50);
    pointLight.position.set(0, 5, 10);
    scene.add(pointLight);

    // Central Router Hub
    const hubGeometry = new THREE.SphereGeometry(0.8, 32, 32);
    const hubMaterial = new THREE.MeshPhongMaterial({ 
        color: 0x00d4ff,
        emissive: 0x0066aa,
        shininess: 100
    });
    const hub = new THREE.Mesh(hubGeometry, hubMaterial);
    scene.add(hub);

    // Hub glow
    const glowGeometry = new THREE.SphereGeometry(1.2, 32, 32);
    const glowMaterial = new THREE.MeshBasicMaterial({
        color: 0x00d4ff,
        transparent: true,
        opacity: 0.15
    });
    const hubGlow = new THREE.Mesh(glowGeometry, glowMaterial);
    scene.add(hubGlow);

    // Interfaces data
    const interfacesData = @json($interfacesData);

    // Create interface nodes in a spiral pattern
    const nodeCount = Math.max(interfacesData.length, 1);
    const interfaces = [];

    interfacesData.forEach((iface, index) => {
        const angle = (2 * Math.PI / nodeCount) * index;
        const radius = 5 + (index % 2) * 2;
        const y = Math.sin(angle * 3) * 2;
        const x = radius * Math.cos(angle);
        const z = radius * Math.sin(angle);

        // Node sphere - different shapes for different types
        let geometry;
        switch(iface.type) {
            case 'wifi':
                geometry = new THREE.ConeGeometry(0.4, 0.8, 8);
                break;
            case 'bridge':
                geometry = new THREE.BoxGeometry(0.6, 0.6, 0.6);
                break;
            case 'vlan':
                geometry = new THREE.TorusGeometry(0.3, 0.1, 8, 16);
                break;
            default: // ethernet
                geometry = new THREE.CylinderGeometry(0.3, 0.3, 0.8, 8);
        }
        
        const material = new THREE.MeshPhongMaterial({ 
            color: iface.color,
            emissive: iface.color,
            emissiveIntensity: 0.3,
            shininess: 80
        });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(x, y, z);
        mesh.userData = iface;
        scene.add(mesh);
        interfaces.push(mesh);

        // Glow effect
        const nodeGlowGeo = new THREE.SphereGeometry(0.6, 16, 16);
        const nodeGlowMat = new THREE.MeshBasicMaterial({
            color: iface.color,
            transparent: true,
            opacity: 0.2
        });
        const nodeGlow = new THREE.Mesh(nodeGlowGeo, nodeGlowMat);
        nodeGlow.position.set(x, y, z);
        scene.add(nodeGlow);

        // Connection to hub
        const lineGeometry = new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(0, 0, 0),
            new THREE.Vector3(x, y, z)
        ]);
        const lineMaterial = new THREE.LineBasicMaterial({ 
            color: iface.color,
            transparent: true,
            opacity: 0.3
        });
        const line = new THREE.Line(lineGeometry, lineMaterial);
        scene.add(line);

        // Data flow animation
        const packetGeometry = new THREE.SphereGeometry(0.08, 8, 8);
        const packetMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff });
        const packet = new THREE.Mesh(packetGeometry, packetMaterial);
        packet.userData = { 
            start: new THREE.Vector3(x, y, z), 
            end: new THREE.Vector3(0, 0, 0),
            progress: Math.random(),
            speed: 0.005 + Math.random() * 0.005
        };
        scene.add(packet);
        
        packet.userData.animate = () => {
            packet.userData.progress += packet.userData.speed;
            if (packet.userData.progress > 1) packet.userData.progress = 0;
            
            const t = packet.userData.progress;
            packet.position.lerpVectors(packet.userData.start, packet.userData.end, t);
            
            // Pulse effect
            const scale = 1 + Math.sin(Date.now() * 0.01) * 0.3;
            packet.scale.set(scale, scale, scale);
        };
        
        interfaces.push(packet);
    });

    // Animation
    let isActive = true;
    const animate = () => {
        if (!isActive) return;
        requestAnimationFrame(animate);

        // Rotate hub
        hub.rotation.y += 0.003;
        hubGlow.rotation.y -= 0.002;

        // Rotate scene slowly
        scene.rotation.y += 0.001;

        // Animate packets
        interfaces.forEach(obj => {
            if (obj.userData.animate) obj.userData.animate();
        });

        renderer.render(scene, camera);
    };

    animate();

    // Handle resize
    const handleResize = () => {
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    };
    window.addEventListener('resize', handleResize);

    // Cleanup on page hide
    document.addEventListener('visibilitychange', () => {
        isActive = document.visibilityState === 'visible';
        if (isActive) animate();
    });
})();
</script>

<!-- Modal functions -->
<script>
// Keep the original modal functions from the file
window.openModal = function(action, id = null) {
    console.log('🖱️ openModal appelé', action, id);
    
    const modal = document.getElementById('interfaceModal');
    const form = document.getElementById('interfaceForm');
    
    if (!modal) {
        console.error('❌ Modal non trouvé!');
        return;
    }

    const title = document.getElementById('modalTitle');
    const methodInput = document.getElementById('method');

    if (action === 'add') {
        title.innerHTML = '<i class="fas fa-plus-circle"></i> Ajouter une interface';
        form.action = "{{ route('interfaces.store') }}";
        methodInput.value = 'POST';
        form.reset();
        toggleWifiFields();
    } else if (action === 'edit' && id) {
        title.innerHTML = '<i class="fas fa-edit"></i> Modifier l\'interface';
        form.action = "/interfaces/" + id;
        methodInput.value = 'PUT';
        
        fetch(`/interfaces/${id}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('routeur_id').value = data.routeur_id || '';
            document.getElementById('nom').value = data.nom || '';
            document.getElementById('type').value = data.type || 'ethernet';
            document.getElementById('adresse_mac').value = data.adresse_mac || '';
            document.getElementById('adresse_ip').value = data.adresse_ip || '';
            document.getElementById('mask').value = data.mask || '';
            document.getElementById('vlan_id').value = data.vlan_id || '';
            document.getElementById('statut').value = data.statut || 'actif';
            document.getElementById('description').value = data.description || '';
            
            if (data.type === 'wifi') {
                document.getElementById('bande').value = data.bande || '2.4GHz';
                document.getElementById('ssid').value = data.ssid || '';
            }
            toggleWifiFields();
        })
        .catch(error => {
            console.error('❌ Erreur chargement:', error);
            alert('Impossible de charger les données de l\'interface');
        });
    }

    modal.style.display = 'flex';
};

window.closeModal = function() {
    const modal = document.getElementById('interfaceModal');
    if (modal) {
        modal.style.display = 'none';
    }
};

window.editInterface = function(id) {
    openModal('edit', id);
};

window.deleteInterface = function(id) {
    if (confirm('Supprimer cette interface ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/interfaces/${id}`;
        form.innerHTML = '@csrf @method('DELETE')';
        document.body.appendChild(form);
        form.submit();
    }
};

window.toggleInterface = function(id) {
    window.location.href = `/interfaces/${id}/toggle`;
};

window.toggleWifiFields = function() {
    const type = document.getElementById('type');
    const wifiFields = document.getElementById('wifiFields');
    if (type && wifiFields) {
        wifiFields.style.display = type.value === 'wifi' ? 'block' : 'none';
    }
};

window.onclick = function(event) {
    const modal = document.getElementById('interfaceModal');
    if (event.target == modal) {
        closeModal();
    }
};
</script>

<!-- Modal HTML -->
<div id="interfaceModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #1e293b; border-radius: 1.5rem; padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; border: 1px solid #334155;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 id="modalTitle" style="color: white;"><i class="fas fa-plus-circle text-cyan-400 mr-2"></i> Ajouter une interface</h3>
            <button onclick="closeModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form id="interfaceForm" method="POST" action="{{ route('interfaces.store') }}">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Routeur *</label>
                <select name="routeur_id" id="routeur_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50" required>
                    <option value="">Sélectionner un routeur</option>
                    @foreach($routeurs as $routeur)
                        <option value="{{ $routeur->id }}">{{ $routeur->nom }} ({{ $routeur->adresse_ip }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Nom de l'interface *</label>
                <input type="text" name="nom" id="nom" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50" placeholder="ex: ether1, wlan1, bridge-local" required>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Type *</label>
                <select name="type" id="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50" required onchange="toggleWifiFields()">
                    <option value="ethernet">Ethernet</option>
                    <option value="wifi">WiFi</option>
                    <option value="bridge">Bridge</option>
                    <option value="vlan">VLAN</option>
                </select>
            </div>

            <div id="wifiFields" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Bande</label>
                        <select name="bande" id="bande" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50">
                            <option value="2.4GHz">2.4 GHz</option>
                            <option value="5GHz">5 GHz</option>
                            <option value="6GHz">6 GHz</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">SSID</label>
                        <input type="text" name="ssid" id="ssid" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50" placeholder="Nom du réseau WiFi">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Adresse MAC</label>
                <input type="text" name="adresse_mac" id="adresse_mac" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 font-mono" placeholder="00:11:22:33:44:55">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Adresse IP</label>
                <input type="text" name="adresse_ip" id="adresse_ip" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 font-mono" placeholder="192.168.1.1">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Masque</label>
                <input type="text" name="mask" id="mask" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 font-mono" placeholder="255.255.255.0">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">VLAN ID</label>
                <input type="number" name="vlan_id" id="vlan_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50" placeholder="10" min="1" max="4094">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Statut *</label>
                <select name="statut" id="statut" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50" required>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                    <option value="erreur">En erreur</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #94a3b8;">Description</label>
                <textarea name="description" id="description" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 resize-none" rows="3"></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeModal()" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                    <i class="fas fa-times mr-2"></i> Annuler
                </button>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center gap-2">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>
@endsection
