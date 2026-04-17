@extends('layouts.app')

@section('title', 'Routeurs - Infrastructure Réseau')

@php
$statusColors = [
    'en_ligne' => 'emerald',
    'hors_ligne' => 'rose',
    'maintenance' => 'amber'
];
$statusLabels = [
    'en_ligne' => 'En ligne',
    'hors_ligne' => 'Hors ligne',
    'maintenance' => 'Maintenance'
];
@endphp

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20" x-data="{ show3D: true, viewMode: 'grid' }">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                <i class="fas fa-network-wired mr-3"></i>Infrastructure Réseau
            </h1>
            <p class="text-slate-400 mt-1">Gestion centralisée des routeurs MikroTik</p>
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
            
            <button onclick="openCreateModal()" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center gap-2 shadow-lg shadow-cyan-500/25">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Nouveau routeur</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Online -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">En ligne</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['en_ligne'] ?? 0) }}</div>
                <div class="text-emerald-400/70 text-sm mt-1">Routeurs actifs</div>
            </div>
        </div>

        <!-- Offline -->
        <div class="bg-gradient-to-br from-rose-500/10 to-rose-600/5 border border-rose-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-rose-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-rose-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-rose-400 text-xl"></i>
                    </div>
                    <span class="text-rose-400 text-sm font-medium">Hors ligne</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['hors_ligne'] ?? 0) }}</div>
                <div class="text-rose-400/70 text-sm mt-1">Nécessite attention</div>
            </div>
        </div>

        <!-- Maintenance -->
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tools text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Maintenance</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['maintenance'] ?? 0) }}</div>
                <div class="text-amber-400/70 text-sm mt-1">En intervention</div>
            </div>
        </div>

        <!-- Total -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-server text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Total</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format(($stats['en_ligne'] ?? 0) + ($stats['hors_ligne'] ?? 0) + ($stats['maintenance'] ?? 0)) }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">{{ $stats['modeles'] ?? 0 }} modèles</div>
            </div>
        </div>
    </div>

    <!-- 3D Network Topology -->
    <div x-show="show3D" x-transition class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-project-diagram text-cyan-400"></i>
                Topologie réseau 3D
            </h3>
            <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
        </div>
        <div id="network3D" class="h-80 w-full"></div>
    </div>

    <!-- Filters -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-4 mb-6">
        <form method="GET" action="{{ route('routeurs.index') }}" class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Rechercher par nom, IP, modèle..."
                       class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition">
            </div>
            
            <div class="flex gap-3">
                <select name="statut" onchange="this.form.submit()" 
                        class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50 min-w-[150px]">
                    <option value="">Tous les statuts</option>
                    <option value="en_ligne" {{ request('statut') == 'en_ligne' ? 'selected' : '' }}>🟢 En ligne</option>
                    <option value="hors_ligne" {{ request('statut') == 'hors_ligne' ? 'selected' : '' }}>🔴 Hors ligne</option>
                    <option value="maintenance" {{ request('statut') == 'maintenance' ? 'selected' : '' }}>🟡 Maintenance</option>
                </select>
                
                <select name="modele" onchange="this.form.submit()" 
                        class="bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50 min-w-[150px]">
                    <option value="">Tous les modèles</option>
                    @foreach($modeles as $modele)
                        <option value="{{ $modele }}" {{ request('modele') == $modele ? 'selected' : '' }}>{{ $modele }}</option>
                    @endforeach
                </select>
                
                @if(request()->anyFilled(['search', 'statut', 'modele']))
                    <a href="{{ route('routeurs.index') }}" class="px-4 py-3 bg-slate-700 hover:bg-slate-600 rounded-xl transition flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid View -->
    <div x-show="viewMode === 'grid'" x-transition class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-8">
        @forelse($routeurs as $routeur)
        @php
            $statusColor = $statusColors[$routeur->statut] ?? 'slate';
            $statusLabel = $statusLabels[$routeur->statut] ?? $routeur->statut;
        @endphp
        <div class="bg-slate-800/50 border border-slate-700 hover:border-{{ $statusColor }}-500/50 rounded-2xl p-5 transition group relative overflow-hidden">
            <!-- Status glow -->
            <div class="absolute top-0 right-0 w-40 h-40 bg-{{ $statusColor }}-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl group-hover:bg-{{ $statusColor }}-500/20 transition"></div>
            
            <div class="relative">
                <!-- Header -->
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-{{ $statusColor }}-500/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-router text-{{ $statusColor }}-400 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-white">{{ $routeur->nom }}</h4>
                            <span class="text-xs text-{{ $statusColor }}-400 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-{{ $statusColor }}-400 animate-pulse"></span>
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <button onclick="editRouteur({{ $routeur->id }})" class="p-2 hover:bg-slate-700 rounded-lg transition text-slate-400 hover:text-white" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteRouteur({{ $routeur->id }})" class="p-2 hover:bg-rose-500/20 rounded-lg transition text-slate-400 hover:text-rose-400" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Info -->
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Modèle</span>
                        <span class="text-white">{{ $routeur->modele ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">IP</span>
                        <span class="text-cyan-400 font-mono">{{ $routeur->adresse_ip }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Version ROS</span>
                        <span class="text-white">{{ $routeur->version_ros ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Uptime</span>
                        <span class="text-white">{{ $routeur->uptime ? floor($routeur->uptime / 86400) . ' jours' : 'N/A' }}</span>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('routeurs.show', $routeur) }}" class="flex-1 px-3 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm text-center transition">
                        <i class="fas fa-eye mr-1"></i> Détails
                    </a>
                    <a href="{{ route('routeurs.interfaces', $routeur) }}" class="px-3 py-2 bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-400 rounded-lg text-sm transition" title="Interfaces">
                        <i class="fas fa-ethernet"></i>
                    </a>
                    <a href="{{ route('routeurs.routes', $routeur) }}" class="px-3 py-2 bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-400 rounded-lg text-sm transition" title="Routes">
                        <i class="fas fa-route"></i>
                    </a>
                    <a href="{{ route('routeurs.firewall', $routeur) }}" class="px-3 py-2 bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 rounded-lg text-sm transition" title="Firewall">
                        <i class="fas fa-shield-alt"></i>
                    </a>
                    <a href="{{ route('routeurs.wifi-zones', $routeur) }}" class="px-3 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 rounded-lg text-sm transition" title="WiFi">
                        <i class="fas fa-wifi"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16">
            <div class="w-20 h-20 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-router text-slate-600 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Aucun routeur trouvé</h3>
            <p class="text-slate-400 mb-6">Commencez par ajouter un nouveau routeur à votre infrastructure</p>
            <button onclick="openCreateModal()" class="px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl font-medium transition inline-block">
                <i class="fas fa-plus mr-2"></i>Ajouter un routeur
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
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Routeur</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Statut</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">IP</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Modèle</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Uptime</th>
                        <th class="text-center px-6 py-4 text-sm font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($routeurs as $routeur)
                    @php
                        $statusColor = $statusColors[$routeur->statut] ?? 'slate';
                        $statusLabel = $statusLabels[$routeur->statut] ?? $routeur->statut;
                    @endphp
                    <tr class="hover:bg-slate-800/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-{{ $statusColor }}-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-router text-{{ $statusColor }}-400"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-white">{{ $routeur->nom }}</div>
                                    <div class="text-xs text-slate-400">{{ $routeur->version_ros ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-{{ $statusColor }}-500/20 text-{{ $statusColor }}-400 rounded-full text-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-{{ $statusColor }}-400 animate-pulse"></span>
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-cyan-400 font-mono">{{ $routeur->adresse_ip }}</td>
                        <td class="px-6 py-4 text-slate-300">{{ $routeur->modele ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-slate-300">{{ $routeur->uptime ? floor($routeur->uptime / 86400) . ' jours' : 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('routeurs.show', $routeur) }}" class="p-2 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-white transition" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="editRouteur({{ $routeur->id }})" class="p-2 hover:bg-slate-700 rounded-lg text-slate-400 hover:text-white transition" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="{{ route('routeurs.interfaces', $routeur) }}" class="p-2 hover:bg-indigo-500/20 rounded-lg text-indigo-400 transition" title="Interfaces">
                                    <i class="fas fa-ethernet"></i>
                                </a>
                                <button onclick="deleteRouteur({{ $routeur->id }})" class="p-2 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-router text-slate-600 text-2xl"></i>
                            </div>
                            <p class="text-slate-400">Aucun routeur trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($routeurs->hasPages())
    <div class="flex items-center justify-between">
        <div class="text-sm text-slate-400">
            Page {{ $routeurs->currentPage() }} sur {{ $routeurs->lastPage() }} • {{ $routeurs->total() }} routeur{{ $routeurs->total() > 1 ? 's' : '' }}
        </div>
        <div class="flex gap-2">
            @if($routeurs->onFirstPage())
                <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 text-slate-600 cursor-not-allowed">
                    <i class="fas fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $routeurs->previousPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
            @endif
            
            @php
                $start = max(1, $routeurs->currentPage() - 2);
                $end = min($routeurs->lastPage(), $routeurs->currentPage() + 2);
            @endphp
            
            @if($start > 1)
                <a href="{{ $routeurs->url(1) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition text-sm">1</a>
                @if($start > 2)
                    <span class="w-10 h-10 flex items-center justify-center text-slate-600">...</span>
                @endif
            @endif
            
            @for($i = $start; $i <= $end; $i++)
                @if($i == $routeurs->currentPage())
                    <span class="w-10 h-10 flex items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-400 font-medium">{{ $i }}</span>
                @else
                    <a href="{{ $routeurs->url($i) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition text-sm">{{ $i }}</a>
                @endif
            @endfor
            
            @if($end < $routeurs->lastPage())
                @if($end < $routeurs->lastPage() - 1)
                    <span class="w-10 h-10 flex items-center justify-center text-slate-600">...</span>
                @endif
                <a href="{{ $routeurs->url($routeurs->lastPage()) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition text-sm">{{ $routeurs->lastPage() }}</a>
            @endif
            
            @if($routeurs->hasMorePages())
                <a href="{{ $routeurs->nextPageUrl() }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
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

<!-- Modal: Créer un routeur -->
<div id="createRouteurModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-slate-700">
            <h3 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-plus-circle text-cyan-400"></i>
                Nouveau Routeur
            </h3>
            <button onclick="closeCreateModal()" class="text-slate-400 hover:text-white transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form action="{{ route('routeurs.store') }}" method="POST" class="p-6">
            @csrf

            <!-- Error Messages -->
            @if($errors->any())
                <div class="mb-4 p-4 bg-rose-500/20 border border-rose-500/50 rounded-xl">
                    <div class="flex items-center gap-2 text-rose-400 mb-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span class="font-medium">Erreurs de validation:</span>
                    </div>
                    <ul class="text-sm text-rose-300 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-xl text-emerald-400">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            <div class="mb-4 p-3 bg-cyan-500/10 border border-cyan-500/30 rounded-xl">
                <p class="text-sm text-cyan-400">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Auto-découverte :</strong> Le modèle, version, MAC et interfaces seront automatiquement récupérés depuis le routeur via l'API.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nom -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nom du routeur <span class="text-rose-400">*</span></label>
                    <input type="text" name="nom" required value="{{ old('nom') }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition"
                           placeholder="Ex: Routeur-Principal-Siege">
                </div>

                <!-- Adresse IP -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Adresse IP <span class="text-rose-400">*</span></label>
                    <input type="text" name="adresse_ip" required value="{{ old('adresse_ip') }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition font-mono"
                           placeholder="Ex: 192.168.88.1">
                </div>

                <!-- API User -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">API User <span class="text-rose-400">*</span></label>
                    <input type="text" name="api_user" required value="{{ old('api_user', 'admin') }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition"
                           placeholder="admin">
                </div>

                <!-- API Password -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">API Password <span class="text-rose-400">*</span></label>
                    <input type="password" name="api_password" required value="{{ old('api_password') }}"
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition"
                           placeholder="Mot de passe API">
                </div>

                <!-- Champs optionnels cachés par défaut -->
                <div class="md:col-span-2">
                    <button type="button" onclick="toggleOptionalFields()" class="text-sm text-slate-400 hover:text-cyan-400 transition flex items-center gap-2">
                        <i class="fas fa-chevron-down" id="optionalIcon"></i>
                        Champs optionnels (manuel)
                    </button>
                </div>

                <div id="optionalFields" class="hidden md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Modèle -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Modèle (auto-détecté)</label>
                        <input type="text" name="modele" value="{{ old('modele') }}"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-400 placeholder-slate-600"
                               placeholder="Sera détecté automatiquement">
                    </div>

                    <!-- Version ROS -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Version RouterOS (auto-détectée)</label>
                        <input type="text" name="version_ros" value="{{ old('version_ros') }}"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-400 placeholder-slate-600"
                               placeholder="Sera détectée automatiquement">
                    </div>

                    <!-- Adresse MAC -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Adresse MAC (auto-détectée)</label>
                        <input type="text" name="adresse_mac" value="{{ old('adresse_mac') }}"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-400 placeholder-slate-600 font-mono"
                               placeholder="Sera détectée automatiquement">
                    </div>

                    <!-- Numéro de série -->
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Numéro de série (auto-détecté)</label>
                        <input type="text" name="numero_serie" value="{{ old('numero_serie') }}"
                               class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-slate-400 placeholder-slate-600"
                               placeholder="Sera détecté automatiquement">
                    </div>

                    <!-- Emplacement -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Emplacement</label>
                        <input type="text" name="emplacement" value="{{ old('emplacement') }}"
                               class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition"
                               placeholder="Ex: Datacenter Principal, Rack 3U">
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Description</label>
                        <textarea name="description" rows="2"
                                  class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition resize-none"
                                  placeholder="Notes ou informations complémentaires...">{{ old('description') }}</textarea>
                    </div>
                </div>

                <!-- Force Create (if IP already exists) -->
                <div class="md:col-span-2">
                    <label class="flex items-center gap-3 p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl cursor-pointer hover:bg-amber-500/20 transition">
                        <input type="checkbox" name="force_create" value="1" {{ old('force_create') ? 'checked' : '' }}
                               class="w-5 h-5 rounded border-amber-500/50 bg-slate-900 text-amber-500 focus:ring-amber-500/20">
                        <div>
                            <span class="text-amber-400 font-medium text-sm">Forcer la création</span>
                            <p class="text-amber-400/70 text-xs">Supprimer le routeur existant avec cette IP si doublon</p>
                        </div>
                    </label>
                </div>
            </div>

            <script>
            function toggleOptionalFields() {
                const fields = document.getElementById('optionalFields');
                const icon = document.getElementById('optionalIcon');
                if (fields.classList.contains('hidden')) {
                    fields.classList.remove('hidden');
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                } else {
                    fields.classList.add('hidden');
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
            </script>

            <div class="flex gap-3 mt-6 pt-4 border-t border-slate-700">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 rounded-xl transition">
                    Annuler
                </button>
                <button type="submit" class="flex-1 px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('createRouteurModal').classList.remove('hidden');
    document.getElementById('createRouteurModal').classList.add('flex');
}

function closeCreateModal() {
    document.getElementById('createRouteurModal').classList.add('hidden');
    document.getElementById('createRouteurModal').classList.remove('flex');
}

// Auto-open modal if ?create=1 is in URL OR if there are validation errors
@if($errors->any() || session('open_modal'))
document.addEventListener('DOMContentLoaded', function() {
    openCreateModal();
});
@else
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('create') === '1') {
        openCreateModal();
        // Clean URL without reload
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
@endif

// Close modal on outside click
document.getElementById('createRouteurModal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});
</script>

<!-- Three.js for 3D Network -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// 3D Network Topology
(function() {
    const container = document.getElementById('network3D');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1e293b);
    
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.z = 15;
    camera.position.y = 5;
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 2);
    scene.add(ambientLight);
    
    const pointLight = new THREE.PointLight(0x00d4ff, 2, 50);
    pointLight.position.set(0, 10, 10);
    scene.add(pointLight);

    // Central Hub
    const hubGeometry = new THREE.SphereGeometry(1, 32, 32);
    const hubMaterial = new THREE.MeshPhongMaterial({ 
        color: 0x00d4ff,
        emissive: 0x0066aa,
        shininess: 100
    });
    const hub = new THREE.Mesh(hubGeometry, hubMaterial);
    scene.add(hub);

    // Hub glow effect
    const glowGeometry = new THREE.SphereGeometry(1.5, 32, 32);
    const glowMaterial = new THREE.MeshBasicMaterial({
        color: 0x00d4ff,
        transparent: true,
        opacity: 0.2
    });
    const hubGlow = new THREE.Mesh(glowGeometry, glowMaterial);
    scene.add(hubGlow);

    // Routers data from PHP
    const routersData = @json($routeurs->map(function($r) {
        return [
            'name' => $r->nom,
            'status' => $r->statut,
            'id' => $r->id
        ];
    }));

    const statusColors = {
        'en_ligne': 0x2ef75b,
        'hors_ligne': 0xff5e7c,
        'maintenance': 0xffaa33
    };

    // Create router nodes
    const routerCount = Math.max(routersData.length, 1);
    const radius = 8;
    const routers = [];

    routersData.forEach((router, index) => {
        const angle = (2 * Math.PI / routerCount) * index;
        const x = radius * Math.cos(angle);
        const z = radius * Math.sin(angle);
        const y = Math.sin(angle * 2) * 2;

        // Router sphere
        const geometry = new THREE.SphereGeometry(0.6, 16, 16);
        const color = statusColors[router.status] || 0x94a3b8;
        const material = new THREE.MeshPhongMaterial({ 
            color: color,
            emissive: color,
            emissiveIntensity: 0.3,
            shininess: 80
        });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(x, y, z);
        mesh.userData = router;
        scene.add(mesh);
        routers.push(mesh);

        // Glow
        const glowGeo = new THREE.SphereGeometry(0.9, 16, 16);
        const glowMat = new THREE.MeshBasicMaterial({
            color: color,
            transparent: true,
            opacity: 0.15
        });
        const glow = new THREE.Mesh(glowGeo, glowMat);
        glow.position.set(x, y, z);
        scene.add(glow);

        // Connection line to hub
        const lineGeometry = new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(0, 0, 0),
            new THREE.Vector3(x, y, z)
        ]);
        const lineMaterial = new THREE.LineBasicMaterial({ 
            color: color,
            transparent: true,
            opacity: 0.4
        });
        const line = new THREE.Line(lineGeometry, lineMaterial);
        scene.add(line);

        // Data packet animation
        const packetGeometry = new THREE.SphereGeometry(0.15, 8, 8);
        const packetMaterial = new THREE.MeshBasicMaterial({ color: 0xffffff });
        const packet = new THREE.Mesh(packetGeometry, packetMaterial);
        packet.userData = { 
            start: new THREE.Vector3(x, y, z), 
            end: new THREE.Vector3(0, 0, 0),
            progress: Math.random()
        };
        scene.add(packet);
        
        // Animate packet
        const animatePacket = () => {
            packet.userData.progress += 0.01;
            if (packet.userData.progress > 1) packet.userData.progress = 0;
            
            const t = packet.userData.progress;
            packet.position.lerpVectors(packet.userData.start, packet.userData.end, t);
            
            // Only show if router is online
            packet.visible = router.status === 'en_ligne';
        };
        
        // Store animate function
        packet.userData.animate = animatePacket;
        routers.push(packet);
    });

    // Animation
    let isActive = true;
    const animate = () => {
        if (!isActive) return;
        requestAnimationFrame(animate);

        // Rotate hub
        hub.rotation.y += 0.005;
        hubGlow.rotation.y -= 0.003;

        // Rotate entire scene slowly
        scene.rotation.y += 0.001;

        // Animate packets
        routers.forEach(obj => {
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

<!-- Modal functions (placeholder - implement as needed) -->
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

function editRouteur(id) {
    window.location.href = `/routeurs/${id}/edit`;
}

async function deleteRouteur(id) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce routeur ?')) return;
    
    try {
        const response = await fetch(`/routeurs/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            location.reload();
        } else {
            alert('Erreur lors de la suppression');
        }
    } catch (e) {
        alert('Erreur: ' + e.message);
    }
}
</script>
@endsection
