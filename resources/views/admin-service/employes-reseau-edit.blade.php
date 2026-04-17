@extends('layouts.app')

@section('title', 'Modifier Employé Réseau - ' . $employe->fullName())

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <i class="fas fa-user-edit text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Modifier Employé Réseau</h1>
                    <p class="text-slate-400">{{ $employe->fullName() }} - {{ $employe->email }}</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin-service.employes-reseau.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all duration-300 flex items-center gap-2 border border-slate-700">
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
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl p-6 shadow-xl">
        <form action="{{ route('admin-service.employes-reseau.update', $employe) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                        <i class="fas fa-user text-blue-400"></i>
                        Nom <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="nom" value="{{ $employe->nom }}" required
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                        <i class="fas fa-user text-blue-400"></i>
                        Prénom <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" name="prenom" value="{{ $employe->prenom }}" required
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                        <i class="fas fa-envelope text-blue-400"></i>
                        Email <span class="text-rose-400">*</span>
                    </label>
                    <input type="email" name="email" value="{{ $employe->email }}" required
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                        <i class="fas fa-phone text-blue-400"></i>
                        Téléphone
                    </label>
                    <input type="tel" name="telephone" value="{{ $employe->telephone }}"
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                        <i class="fas fa-id-badge text-blue-400"></i>
                        Matricule
                    </label>
                    <input type="text" name="matricule" value="{{ $employe->matricule }}"
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                        <i class="fas fa-building text-blue-400"></i>
                        Département
                    </label>
                    <input type="text" name="departement" value="{{ $employe->departement }}"
                           class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                    <i class="fas fa-briefcase text-blue-400"></i>
                    Poste
                </label>
                <input type="text" name="poste" value="{{ $employe->poste }}"
                       class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
            </div>

            <!-- WiFi Configuration -->
            <div class="border-t border-slate-700 pt-6">
                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-wifi text-cyan-400"></i>
                    Configuration WiFi
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                            <i class="fas fa-wifi text-cyan-400"></i>
                            Zone WiFi <span class="text-rose-400">*</span>
                        </label>
                        <select name="wifi_zone_id" required
                                class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                            @foreach($zonesWifi as $zone)
                                <option value="{{ $zone->id }}" {{ $employe->wifi_zone_id == $zone->id ? 'selected' : '' }}>
                                    {{ $zone->nom }} ({{ $zone->ssid }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                            <i class="fas fa-ethernet text-cyan-400"></i>
                            Adresse MAC
                        </label>
                        <input type="text" name="mac_address" value="{{ $employe->mac_address }}" placeholder="XX:XX:XX:XX:XX:XX"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>
            </div>

            <!-- Bandwidth Configuration -->
            <div class="border-t border-slate-700 pt-6">
                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-tachometer-alt text-emerald-400"></i>
                    Bande Passante Personnalisée
                </h4>
                <p class="text-sm text-slate-500 mb-4">Laissez à 0 pour utiliser les valeurs de la zone WiFi</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                            <i class="fas fa-download text-cyan-400"></i>
                            Download (Mbps)
                        </label>
                        <input type="number" name="bandwidth_down" value="{{ $employe->bandwidth_down }}" min="0"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                        <p class="text-xs text-slate-500">Actuel: {{ $employe->bandwidthFormatted() }}</p>
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                            <i class="fas fa-upload text-emerald-400"></i>
                            Upload (Mbps)
                        </label>
                        <input type="number" name="bandwidth_up" value="{{ $employe->bandwidth_up }}" min="0"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                            <i class="fas fa-database text-amber-400"></i>
                            Quota Mensuel (Mo)
                        </label>
                        <input type="number" name="quota_monthly" value="{{ $employe->quota_monthly }}" min="0"
                               class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                        <p class="text-xs text-slate-500">Actuel: {{ $employe->quotaFormatted() }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            @if($employe->data_used_this_month > 0 || $employe->last_connected_at)
            <div class="border-t border-slate-700 pt-6">
                <h4 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-purple-400"></i>
                    Statistiques
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-slate-900/50 rounded-xl p-4">
                        <p class="text-sm text-slate-400 mb-1">Données ce mois</p>
                        <p class="text-xl font-bold text-white">{{ $employe->dataUsedFormatted() }}</p>
                        @if($employe->quota_monthly > 0)
                            @php $percent = $employe->quotaUsedPercent(); @endphp
                            <div class="mt-2 h-2 bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $percent > 80 ? 'bg-rose-500' : ($percent > 50 ? 'bg-amber-500' : 'bg-emerald-500') }} rounded-full" 
                                     style="width: {{ min(100, $percent) }}%"></div>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">{{ number_format($percent, 1) }}% du quota</p>
                        @endif
                    </div>
                    <div class="bg-slate-900/50 rounded-xl p-4">
                        <p class="text-sm text-slate-400 mb-1">Dernière connexion</p>
                        <p class="text-xl font-bold text-white">{{ $employe->last_connected_at ? $employe->last_connected_at->diffForHumans() : 'Jamais' }}</p>
                    </div>
                    <div class="bg-slate-900/50 rounded-xl p-4">
                        <p class="text-sm text-slate-400 mb-1">Temps de connexion</p>
                        <p class="text-xl font-bold text-white">{{ $employe->connectionDurationFormatted() }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notes -->
            <div class="border-t border-slate-700 pt-6">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                        <i class="fas fa-sticky-note text-slate-400"></i>
                        Notes
                    </label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">{{ $employe->notes }}</textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="border-t border-slate-700 pt-6 flex justify-end gap-3">
                <a href="{{ route('admin-service.employes-reseau.index') }}" 
                   class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-xl transition-all duration-300 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2.5 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-400 hover:to-cyan-500 text-white rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg shadow-blue-500/25">
                    <i class="fas fa-check"></i>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
</file_content>
