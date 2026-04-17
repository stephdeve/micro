@extends('layouts.app')

@section('title', 'Employés - ' . ($service->nom ?? 'Service'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <i class="fas fa-users text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Employés</h1>
                    <p class="text-slate-400">Service : {{ $service->nom ?? '—' }} | {{ $employes->total() ?? 0 }} employé(s)</p>
                </div>
            </div>
        </div>
        <a href="{{ route('admin-service.employes.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white rounded-xl transition-all duration-300 flex items-center gap-2 shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40">
            <i class="fas fa-plus"></i>
            <span>Ajouter employé</span>
        </a>
    </div>

    <!-- Alerts -->
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl p-4 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Total</p>
                    <p class="text-2xl font-bold text-white">{{ $employes->total() ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-users text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl p-4 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Actifs</p>
                    <p class="text-2xl font-bold text-emerald-400">
                        {{ count(array_filter(($employes ?? collect())->all(), fn($e) => $e->est_actif)) }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-500/20 flex items-center justify-center">
                    <i class="fas fa-user-check text-emerald-400 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl p-4 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Inactifs</p>
                    <p class="text-2xl font-bold text-rose-400">
                        {{ count(array_filter(($employes ?? collect())->all(), fn($e) => !$e->est_actif)) }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-user-slash text-rose-400 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl p-4 shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-400 text-sm">Admin Service</p>
                    <p class="text-2xl font-bold text-amber-400">
                        {{ count(array_filter(($employes ?? collect())->all(), fn($e) => $e->hasRole('admin_service'))) }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-500/20 flex items-center justify-center">
                    <i class="fas fa-user-shield text-amber-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700 rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between bg-gradient-to-r from-blue-500/10 to-cyan-500/10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center">
                    <i class="fas fa-users-cog text-white"></i>
                </div>
                <h3 class="text-lg font-semibold text-white">Liste des employés</h3>
            </div>
            <div class="flex gap-2">
                <input type="text" placeholder="Rechercher..." class="px-4 py-2 bg-slate-900 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 text-sm">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-700 bg-slate-900/50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Employé</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Contact</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Rôle</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-slate-300">Statut</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-slate-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($employes ?? [] as $emp)
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($emp->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $emp->name }}</p>
                                        <p class="text-sm text-slate-400">{{ $emp->fonction ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-slate-300">{{ $emp->email }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($emp->roles as $role)
                                        <span class="px-2 py-1 rounded-lg text-xs font-medium 
                                            @if($role->name == 'admin_service') bg-emerald-500/20 text-emerald-400 border border-emerald-500/30
                                            @elseif($role->name == 'super_admin') bg-purple-500/20 text-purple-400 border border-purple-500/30
                                            @else bg-slate-600/50 text-slate-300 border border-slate-500/30 @endif">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                                    {{ $emp->est_actif ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $emp->est_actif ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                    {{ $emp->est_actif ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin-service.employes.edit', $emp) }}" 
                                       class="w-9 h-9 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 flex items-center justify-center transition-all hover:scale-110"
                                       title="Modifier">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <form action="{{ route('admin-service.employes.destroy', $emp) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="w-9 h-9 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 flex items-center justify-center transition-all hover:scale-110"
                                                title="Supprimer">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-16 h-16 rounded-2xl bg-slate-700/50 flex items-center justify-center">
                                        <i class="fas fa-users text-2xl text-slate-500"></i>
                                    </div>
                                    <p class="text-slate-400">Aucun employé dans ce service</p>
                                    <a href="{{ route('admin-service.employes.create') }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                        Ajouter un employé
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(($employes ?? false) && method_exists($employes, 'links'))
            <div class="px-6 py-4 border-t border-slate-700">
                {{ $employes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
