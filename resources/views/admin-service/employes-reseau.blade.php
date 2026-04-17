@extends('layouts.app')

@section('title', 'Employés Réseau - ' . ($service->nom ?? 'Service'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/25">
                    <i class="fas fa-wifi text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Employés Réseau</h1>
                    <p class="text-slate-400">{{ $service->nom ?? 'Service' }} - Gestion des accès WiFi et bande passante</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <button onclick="openModal('addEmployeModal')" class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40">
                <i class="fas fa-plus"></i>
                <span>Ajouter employé</span>
            </button>
            <a href="{{ route('admin-service.dashboard') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all duration-300 flex items-center gap-2 border border-slate-700">
                <i class="fas fa-arrow-left"></i>
                <span>Retour</span>
            </a>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 flex items-center gap-3">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-center gap-3">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total Employés</p>
                    <p class="text-2xl font-bold text-white">{{ $employesReseau->count() ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-users text-blue-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">En ligne</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ $employesReseau->where('last_connected_at', '>=', now()->subMinutes(5))->count() ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-signal text-emerald-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Bloqués</p>
                    <p class="text-2xl font-bold text-rose-400">{{ $employesReseau->where('active', false)->count() ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-ban text-rose-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Zones WiFi</p>
                    <p class="text-2xl font-bold text-cyan-400">{{ $zonesWifi->count() ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                    <i class="fas fa-wifi text-cyan-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap gap-3">
        <select id="filterZone" onchange="filterEmployes()" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 focus:outline-none focus:border-cyan-500">
            <option value="">Toutes les zones</option>
            @foreach($zonesWifi as $zone)
                <option value="{{ $zone->id }}">{{ $zone->nom }}</option>
            @endforeach
        </select>
        <select id="filterStatus" onchange="filterEmployes()" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 focus:outline-none focus:border-cyan-500">
            <option value="">Tous les statuts</option>
            <option value="online">En ligne</option>
            <option value="offline">Hors ligne</option>
            <option value="blocked">Bloqués</option>
        </select>
        <input type="text" id="searchEmploye" onkeyup="searchEmployes()" placeholder="Rechercher..." class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 placeholder-slate-500 focus:outline-none focus:border-cyan-500">
    </div>

    <!-- Employés Table -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full" id="employesTable">
                <thead>
                    <tr class="bg-slate-700/50 border-b border-slate-600">
                        <th class="px-4 py-4 text-left text-sm font-semibold text-slate-300">Employé</th>
                        <th class="px-4 py-4 text-left text-sm font-semibold text-slate-300">Zone WiFi</th>
                        <th class="px-4 py-4 text-left text-sm font-semibold text-slate-300">Bande Passante</th>
                        <th class="px-4 py-4 text-left text-sm font-semibold text-slate-300">Quota / Consommation</th>
                        <th class="px-4 py-4 text-left text-sm font-semibold text-slate-300">Statut</th>
                        <th class="px-4 py-4 text-center text-sm font-semibold text-slate-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($employesReseau as $employe)
                        <tr class="hover:bg-slate-700/30 transition-colors" data-zone="{{ $employe->wifi_zone_id }}" data-status="{{ $employe->active ? ($employe->last_connected_at && $employe->last_connected_at->gt(now()->subMinutes(5)) ? 'online' : 'offline') : 'blocked' }}">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center">
                                        <i class="fas fa-user text-slate-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $employe->prenom }} {{ $employe->nom }}</p>
                                        <p class="text-sm text-slate-400">{{ $employe->email }}</p>
                                        <p class="text-xs text-slate-500">{{ $employe->matricule ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if($employe->wifiZone)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-500/20 text-cyan-400 text-xs font-medium">
                                        <i class="fas fa-wifi"></i> {{ $employe->wifiZone->nom }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-700 text-slate-400 text-xs">Non assigné</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-sm text-slate-300">
                                    @if($employe->bandwidth_down > 0 || $employe->bandwidth_up > 0)
                                        <span class="text-cyan-400">↓ {{ $employe->bandwidth_down }} Mbps</span>
                                        <span class="text-slate-500 mx-1">/</span>
                                        <span class="text-emerald-400">↑ {{ $employe->bandwidth_up }} Mbps</span>
                                        <span class="text-xs text-slate-500 block">Personnalisé</span>
                                    @elseif($employe->wifiZone)
                                        <span class="text-cyan-400">↓ {{ $employe->wifiZone->bandwidth_down }} Mbps</span>
                                        <span class="text-slate-500 mx-1">/</span>
                                        <span class="text-emerald-400">↑ {{ $employe->wifiZone->bandwidth_up }} Mbps</span>
                                        <span class="text-xs text-slate-500 block">Hérité de la zone</span>
                                    @else
                                        <span class="text-slate-500">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                @if($employe->quota_monthly > 0)
                                    <div class="w-32">
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="text-slate-400">{{ $employe->dataUsedFormatted() }} / {{ $employe->quotaFormatted() }}</span>
                                        </div>
                                        @php
                                            $percent = $employe->quotaUsedPercent();
                                            $color = $percent > 80 ? 'bg-rose-500' : ($percent > 50 ? 'bg-amber-500' : 'bg-emerald-500');
                                        @endphp
                                        <div class="h-2 bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full {{ $color }} rounded-full transition-all" style="width: {{ min(100, $percent) }}%"></div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-500">Illimité</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                @if(!$employe->active)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-500/20 text-rose-400 text-xs font-medium">
                                        <i class="fas fa-ban"></i> Bloqué
                                    </span>
                                @elseif($employe->last_connected_at && $employe->last_connected_at->gt(now()->subMinutes(5)))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-medium">
                                        <i class="fas fa-circle text-[8px] animate-pulse"></i> En ligne
                                    </span>
                                    <span class="text-xs text-slate-500 block mt-1">{{ $employe->connectionDurationFormatted() }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-700 text-slate-400 text-xs">
                                        <i class="fas fa-circle text-[8px]"></i> Hors ligne
                                    </span>
                                    @if($employe->last_connected_at)
                                        <span class="text-xs text-slate-500 block mt-1">{{ $employe->last_connected_at->diffForHumans() }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="showRealtime({{ $employe->id }})" class="w-8 h-8 rounded-lg bg-cyan-500/20 hover:bg-cyan-500/30 text-cyan-400 transition-colors flex items-center justify-center" title="Temps réel">
                                        <i class="fas fa-chart-line text-xs"></i>
                                    </button>
                                    <button onclick="showHistory({{ $employe->id }})" class="w-8 h-8 rounded-lg bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 transition-colors flex items-center justify-center" title="Historique">
                                        <i class="fas fa-history text-xs"></i>
                                    </button>
                                    <button onclick="editEmploye({{ $employe->id }})" class="w-8 h-8 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 transition-colors flex items-center justify-center" title="Modifier">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    @if($employe->active)
                                        <form action="{{ route('admin-service.employes-reseau.toggle', $employe) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 transition-colors flex items-center justify-center" title="Bloquer">
                                                <i class="fas fa-ban text-xs"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin-service.employes-reseau.toggle', $employe) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 transition-colors flex items-center justify-center" title="Débloquer">
                                                <i class="fas fa-check text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin-service.employes-reseau.destroy', $employe) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 transition-colors flex items-center justify-center" title="Supprimer">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-500">
                                    <div class="w-16 h-16 rounded-full bg-slate-800 flex items-center justify-center mb-4">
                                        <i class="fas fa-users text-2xl"></i>
                                    </div>
                                    <p class="text-lg font-medium text-slate-400 mb-1">Aucun employé réseau</p>
                                    <p class="text-sm">Cliquez sur "Ajouter employé" pour créer un nouvel employé</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Ajouter Employé -->
<div id="addEmployeModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="closeModal('addEmployeModal')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-gradient-to-r from-emerald-500/10 to-teal-500/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
                        <i class="fas fa-user-plus text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white">Ajouter un employé réseau</h3>
                </div>
                <button onclick="closeModal('addEmployeModal')" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-400 hover:text-white transition-colors flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                <form action="{{ route('admin-service.employes-reseau.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Nom <span class="text-rose-400">*</span></label>
                        <input type="text" name="nom" required class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Prénom <span class="text-rose-400">*</span></label>
                        <input type="text" name="prenom" required class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Email <span class="text-rose-400">*</span></label>
                        <input type="email" name="email" required class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Téléphone</label>
                        <input type="tel" name="telephone" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Matricule</label>
                        <input type="text" name="matricule" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300">Département</label>
                        <input type="text" name="departement" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">Poste</label>
                    <input type="text" name="poste" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                </div>

                <div class="border-t border-slate-700 pt-4 mt-4">
                    <h4 class="text-sm font-semibold text-slate-300 mb-3 flex items-center gap-2">
                        <i class="fas fa-wifi text-cyan-400"></i> Configuration WiFi
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Zone WiFi <span class="text-rose-400">*</span></label>
                            <select name="wifi_zone_id" required class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                <option value="">Sélectionner une zone</option>
                                @foreach($zonesWifi as $zone)
                                    <option value="{{ $zone->id }}">{{ $zone->nom }} ({{ $zone->ssid }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Adresse MAC</label>
                            <input type="text" name="mac_address" placeholder="XX:XX:XX:XX:XX:XX" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-700 pt-4">
                    <h4 class="text-sm font-semibold text-slate-300 mb-3 flex items-center gap-2">
                        <i class="fas fa-tachometer-alt text-cyan-400"></i> Bande Passante Personnalisée (Optionnel)
                    </h4>
                    <p class="text-xs text-slate-500 mb-3">Laissez vide pour utiliser les valeurs de la zone WiFi</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Download (Mbps)</label>
                            <input type="number" name="bandwidth_down" min="0" placeholder="0 = illimité" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Upload (Mbps)</label>
                            <input type="number" name="bandwidth_up" min="0" placeholder="0 = illimité" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-300">Quota Mensuel (Mo)</label>
                            <input type="number" name="quota_monthly" min="0" placeholder="0 = illimité" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"></textarea>
                </div>

                <div class="border-t border-slate-700 pt-4 flex justify-end gap-3">
                    <button type="button" onclick="closeModal('addEmployeModal')" class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl transition-all duration-300 flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg shadow-emerald-500/25">
                        <i class="fas fa-check"></i>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Temps Réel -->
<div id="realtimeModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="closeModal('realtimeModal')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-3xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-gradient-to-r from-cyan-500/10 to-blue-500/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center animate-pulse">
                        <i class="fas fa-chart-line text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Consommation Temps Réel</h3>
                        <p class="text-sm text-slate-400" id="realtimeEmployeName">—</p>
                    </div>
                </div>
                <button onclick="closeModal('realtimeModal')" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-400 hover:text-white transition-colors flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-slate-900/50 rounded-xl p-4 text-center">
                        <p class="text-slate-400 text-sm mb-1">Download</p>
                        <p class="text-2xl font-bold text-cyan-400" id="realtimeDownload">0 Mbps</p>
                    </div>
                    <div class="bg-slate-900/50 rounded-xl p-4 text-center">
                        <p class="text-slate-400 text-sm mb-1">Upload</p>
                        <p class="text-2xl font-bold text-emerald-400" id="realtimeUpload">0 Mbps</p>
                    </div>
                    <div class="bg-slate-900/50 rounded-xl p-4 text-center">
                        <p class="text-slate-400 text-sm mb-1">Ping</p>
                        <p class="text-2xl font-bold text-amber-400" id="realtimePing">— ms</p>
                    </div>
                    <div class="bg-slate-900/50 rounded-xl p-4 text-center">
                        <p class="text-slate-400 text-sm mb-1">Session</p>
                        <p class="text-2xl font-bold text-white" id="realtimeDuration">—</p>
                    </div>
                </div>

                <div class="bg-slate-900/50 rounded-xl p-4 h-64 flex items-end justify-center">
                    <canvas id="bandwidthChart" class="w-full h-full"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Historique -->
<div id="historyModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="closeModal('historyModal')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-gradient-to-r from-purple-500/10 to-pink-500/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
                        <i class="fas fa-history text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">Historique des Connexions</h3>
                        <p class="text-sm text-slate-400" id="historyEmployeName">—</p>
                    </div>
                </div>
                <button onclick="closeModal('historyModal')" class="w-8 h-8 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-400 hover:text-white transition-colors flex items-center justify-center">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-700">
                                <th class="text-left py-3 text-slate-400">Date/Heure</th>
                                <th class="text-left py-3 text-slate-400">Zone WiFi</th>
                                <th class="text-left py-3 text-slate-400">IP</th>
                                <th class="text-left py-3 text-slate-400">MAC</th>
                                <th class="text-left py-3 text-slate-400">Durée</th>
                                <th class="text-left py-3 text-slate-400">Données</th>
                                <th class="text-left py-3 text-slate-400">Statut</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody" class="divide-y divide-slate-700">
                            <!-- Dynamiquement rempli -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById(modalId).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = '';
}

function filterEmployes() {
    const zoneFilter = document.getElementById('filterZone').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('#employesTable tbody tr');

    rows.forEach(row => {
        const zone = row.getAttribute('data-zone');
        const status = row.getAttribute('data-status');
        
        let show = true;
        if (zoneFilter && zone !== zoneFilter) show = false;
        if (statusFilter && status !== statusFilter) show = false;
        
        row.style.display = show ? '' : 'none';
    });
}

function searchEmployes() {
    const search = document.getElementById('searchEmploye').value.toLowerCase();
    const rows = document.querySelectorAll('#employesTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(search) ? '' : 'none';
    });
}

function showRealtime(employeId) {
    // Simuler des données temps réel
    document.getElementById('realtimeDownload').textContent = (Math.random() * 50 + 10).toFixed(1) + ' Mbps';
    document.getElementById('realtimeUpload').textContent = (Math.random() * 20 + 5).toFixed(1) + ' Mbps';
    document.getElementById('realtimePing').textContent = Math.floor(Math.random() * 50 + 10) + ' ms';
    document.getElementById('realtimeDuration').textContent = Math.floor(Math.random() * 2 + 1) + 'h ' + Math.floor(Math.random() * 59) + 'min';
    
    openModal('realtimeModal');
    
    // Démarre le polling temps réel
    startRealtimePolling(employeId);
}

let realtimeInterval = null;
function startRealtimePolling(employeId) {
    if (realtimeInterval) clearInterval(realtimeInterval);
    
    realtimeInterval = setInterval(() => {
        // Simuler les mises à jour
        document.getElementById('realtimeDownload').textContent = (Math.random() * 50 + 10).toFixed(1) + ' Mbps';
        document.getElementById('realtimeUpload').textContent = (Math.random() * 20 + 5).toFixed(1) + ' Mbps';
    }, 2000);
}

function showHistory(employeId) {
    // Simuler des données d'historique
    const tbody = document.getElementById('historyTableBody');
    tbody.innerHTML = `
        <tr class="hover:bg-slate-700/30">
            <td class="py-3 text-slate-300">2024-01-15 09:30</td>
            <td class="py-3"><span class="px-2 py-1 rounded bg-cyan-500/20 text-cyan-400 text-xs">Zone Employés</span></td>
            <td class="py-3 text-slate-300">192.168.10.45</td>
            <td class="py-3 text-slate-400 font-mono text-xs">AA:BB:CC:DD:EE:FF</td>
            <td class="py-3 text-slate-300">4h 23min</td>
            <td class="py-3 text-slate-300">↓ 1.2 Go / ↑ 340 Mo</td>
            <td class="py-3"><span class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 text-xs">Complété</span></td>
        </tr>
        <tr class="hover:bg-slate-700/30">
            <td class="py-3 text-slate-300">2024-01-14 08:15</td>
            <td class="py-3"><span class="px-2 py-1 rounded bg-cyan-500/20 text-cyan-400 text-xs">Zone Employés</span></td>
            <td class="py-3 text-slate-300">192.168.10.42</td>
            <td class="py-3 text-slate-400 font-mono text-xs">AA:BB:CC:DD:EE:FF</td>
            <td class="py-3 text-slate-300">6h 45min</td>
            <td class="py-3 text-slate-300">↓ 2.1 Go / ↑ 560 Mo</td>
            <td class="py-3"><span class="px-2 py-1 rounded bg-emerald-500/20 text-emerald-400 text-xs">Complété</span></td>
        </tr>
        <tr class="hover:bg-slate-700/30">
            <td class="py-3 text-slate-300">2024-01-13 10:00</td>
            <td class="py-3"><span class="px-2 py-1 rounded bg-amber-500/20 text-amber-400 text-xs">Visiteurs</span></td>
            <td class="py-3 text-slate-300">192.168.20.15</td>
            <td class="py-3 text-slate-400 font-mono text-xs">AA:BB:CC:DD:EE:FF</td>
            <td class="py-3 text-slate-300">45min</td>
            <td class="py-3 text-slate-300">↓ 125 Mo / ↑ 15 Mo</td>
            <td class="py-3"><span class="px-2 py-1 rounded bg-rose-500/20 text-rose-400 text-xs">Déconnecté</span></td>
        </tr>
    `;
    
    openModal('historyModal');
}

function editEmploye(employeId) {
    // Redirection vers la page d'édition
    window.location.href = `/admin-service/employes-reseau/${employeId}/edit`;
}

// Fermer les modals avec Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id$="Modal"]').forEach(modal => {
            modal.classList.add('hidden');
        });
        document.body.style.overflow = '';
        if (realtimeInterval) clearInterval(realtimeInterval);
    }
});
</script>
@endsection
</file_content>
