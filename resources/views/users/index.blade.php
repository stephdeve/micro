@extends('layouts.app')

@section('title', 'Gestion des employés')

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    @php
        // Calculate stats for charts
        $totalUsers = $users->total();
        $activeUsers = $users->filter(function($u) { return $u->est_actif; })->count();
        $inactiveUsers = $totalUsers - $activeUsers;
        
        $rolesCount = [];
        foreach($users as $user) {
            $role = $user->getRoleNames()->first() ?? 'sans_role';
            $rolesCount[$role] = ($rolesCount[$role] ?? 0) + 1;
        }
        
        // Services count (if user has service relation)
        $serviceCount = [];
        foreach($users as $user) {
            $service = $user->service?->nom ?? 'Non assigné';
            $serviceCount[$service] = ($serviceCount[$service] ?? 0) + 1;
        }
    @endphp

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/25">
                <i class="fas fa-users text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-500 bg-clip-text text-transparent">
                    Gestion des employés
                </h1>
                <p class="text-slate-400 mt-1 flex items-center gap-2">
                    <i class="fas fa-user-shield text-indigo-400/70"></i>
                    Administration des utilisateurs du réseau
                </p>
            </div>
        </div>
        
        <div class="flex gap-3">
            <button onclick="document.getElementById('create-user-modal').classList.remove('hidden'); document.getElementById('create-user-modal').classList.add('flex');" 
                class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 rounded-xl font-medium transition flex items-center gap-2 shadow-lg shadow-emerald-500/25">
                <i class="fas fa-user-plus"></i>
                <span>Nouvel employé</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Users -->
        <div class="bg-gradient-to-br from-indigo-500/10 to-purple-600/5 border border-indigo-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-indigo-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-indigo-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-indigo-400 text-xl"></i>
                    </div>
                    <span class="text-indigo-400 text-sm font-medium">Total</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $totalUsers }}</div>
                <div class="text-indigo-400/70 text-sm mt-1">Employés enregistrés</div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-check text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">Actifs</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $activeUsers }}</div>
                <div class="text-emerald-400/70 text-sm mt-1">Comptes actifs</div>
            </div>
        </div>

        <!-- Inactive Users -->
        <div class="bg-gradient-to-br from-rose-500/10 to-pink-600/5 border border-rose-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-rose-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-rose-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-slash text-rose-400 text-xl"></i>
                    </div>
                    <span class="text-rose-400 text-sm font-medium">Inactifs</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $inactiveUsers }}</div>
                <div class="text-rose-400/70 text-sm mt-1">Comptes désactivés</div>
            </div>
        </div>

        <!-- Roles Count -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-tag text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Rôles</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ count($rolesCount) }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">Types de rôles</div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Roles Distribution -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-chart-pie text-indigo-400"></i>
                    Répartition par rôle
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-48 flex items-center justify-center">
                    <canvas id="rolesChart"></canvas>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach($rolesCount as $role => $count)
                        @php
                            $roleColor = match($role) {
                                'super_admin' => 'indigo',
                                'admin_reseau' => 'cyan',
                                'admin_service' => 'emerald',
                                'employe' => 'amber',
                                default => 'slate'
                            };
                            $roleLabel = str_replace('_', ' ', $role);
                        @endphp
                        <div class="flex items-center justify-between p-2 bg-slate-900/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-{{ $roleColor }}-400"></span>
                                <span class="text-slate-300 text-sm capitalize">{{ $roleLabel }}</span>
                            </div>
                            <span class="text-{{ $roleColor }}-400 font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-toggle-on text-emerald-400"></i>
                    Statut des comptes
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-48 flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="p-3 bg-emerald-500/10 rounded-xl text-center">
                        <div class="text-2xl font-bold text-emerald-400">{{ $activeUsers }}</div>
                        <div class="text-xs text-emerald-400/70">Actifs</div>
                    </div>
                    <div class="p-3 bg-rose-500/10 rounded-xl text-center">
                        <div class="text-2xl font-bold text-rose-400">{{ $inactiveUsers }}</div>
                        <div class="text-xs text-rose-400/70">Inactifs</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Services Distribution -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-building text-cyan-400"></i>
                    Par service
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-48 flex items-center justify-center">
                    <canvas id="servicesChart"></canvas>
                </div>
                <div class="mt-4 max-h-24 overflow-y-auto space-y-1">
                    @foreach(array_slice($serviceCount, 0, 5, true) as $service => $count)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-400 truncate max-w-[150px]">{{ $service }}</span>
                            <span class="text-cyan-400 font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                    @if(count($serviceCount) > 5)
                        <div class="text-center text-xs text-slate-500">+ {{ count($serviceCount) - 5 }} autres</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-list text-purple-400"></i>
                Liste des employés
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-400">{{ $users->total() }} employés</span>
                <div class="flex gap-1">
                    <button onclick="filterUsers('all')" class="px-3 py-1.5 text-xs bg-indigo-500/20 text-indigo-400 rounded-lg hover:bg-indigo-500/30 transition">Tous</button>
                    <button onclick="filterUsers('active')" class="px-3 py-1.5 text-xs bg-emerald-500/20 text-emerald-400 rounded-lg hover:bg-emerald-500/30 transition">Actifs</button>
                    <button onclick="filterUsers('inactive')" class="px-3 py-1.5 text-xs bg-rose-500/20 text-rose-400 rounded-lg hover:bg-rose-500/30 transition">Inactifs</button>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="usersTable">
                <thead class="bg-slate-900/50">
                    <tr>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Employé</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Contact</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Rôle</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Fonction</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Statut</th>
                        <th class="text-center px-4 py-3 text-sm font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($users as $user)
                    @php
                        $role = $user->getRoleNames()->first() ?? 'sans_role';
                        $roleColor = match($role) {
                            'super_admin' => 'indigo',
                            'admin_reseau' => 'cyan',
                            'admin_service' => 'emerald',
                            'employe' => 'amber',
                            default => 'slate'
                        };
                        $initial = strtoupper(substr($user->name, 0, 1));
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition group" data-status="{{ $user->est_actif ? 'active' : 'inactive' }}">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-{{ $roleColor }}-500 to-{{ $roleColor }}-600 rounded-xl flex items-center justify-center font-bold text-white">
                                    {{ $initial }}
                                </div>
                                <div>
                                    <div class="text-white font-medium">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->service?->nom ?? 'Non assigné' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-slate-300 text-sm">{{ $user->email }}</div>
                            <div class="text-xs text-slate-500">{{ $user->telephone ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-1 bg-{{ $roleColor }}-500/20 text-{{ $roleColor }}-400 rounded-lg text-xs capitalize">
                                {{ str_replace('_', ' ', $role) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-slate-300 text-sm">
                            {{ $user->fonction ?? '—' }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs {{ $user->est_actif ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->est_actif ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                                {{ $user->est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('users.edit', $user) }}" class="p-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 rounded-lg transition" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Confirmer la suppression de {{ $user->name }} ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg transition" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-users text-slate-600 text-2xl"></i>
                            </div>
                            <p class="text-slate-400">Aucun employé trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="p-4 border-t border-slate-700">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-sm text-slate-400">
                    Page {{ $users->currentPage() }} sur {{ $users->lastPage() }} — {{ $users->total() }} employés
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ $users->url(1) }}" class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition {{ $users->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="{{ $users->previousPageUrl() }}" class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition {{ $users->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-left"></i>
                    </a>
                    
                    @foreach(range(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page)
                        <a href="{{ $users->url($page) }}" class="w-10 h-10 text-sm flex items-center justify-center rounded-lg transition {{ $users->currentPage() == $page ? 'bg-indigo-500 text-white' : 'bg-slate-800 hover:bg-slate-700 text-slate-300' }}">
                            {{ $page }}
                        </a>
                    @endforeach
                    
                    <a href="{{ $users->nextPageUrl() }}" class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition {{ $users->currentPage() == $users->lastPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="{{ $users->url($users->lastPage()) }}" class="px-3 py-2 text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition {{ $users->currentPage() == $users->lastPage() ? 'opacity-50 pointer-events-none' : '' }}">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>

<!-- Create User Modal -->
<div id="create-user-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="p-6 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-user-plus text-emerald-400"></i>
                Nouvel employé
            </h3>
            <button onclick="closeModal()" class="p-2 hover:bg-slate-700 rounded-lg transition">
                <i class="fas fa-times text-slate-400"></i>
            </button>
        </div>
        
        <form method="POST" action="{{ route('users.store') }}" class="p-6 space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm text-slate-400 mb-2">Nom complet *</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="text" name="name" required value="{{ old('name') }}" 
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
            </div>
            
            <div>
                <label class="block text-sm text-slate-400 mb-2">Email *</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="email" name="email" required value="{{ old('email') }}" 
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Mot de passe *</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password" required 
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Confirmer *</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password_confirmation" required 
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Téléphone</label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="telephone" value="{{ old('telephone') }}" 
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Fonction</label>
                    <div class="relative">
                        <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="fonction" value="{{ old('fonction') }}" 
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-2">Rôle *</label>
                    <div class="relative">
                        <i class="fas fa-user-shield absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <select name="role" required 
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition appearance-none">
                            <option value="">Choisir...</option>
                            @foreach($roles ?? [] as $role)
                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role)) }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    </div>
                </div>
                <div class="flex items-center">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="est_actif" value="1" {{ old('est_actif') ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-12 h-6 bg-slate-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full peer-checked:translate-x-6 transition-transform"></div>
                        </div>
                        <span class="text-slate-300">Compte actif</span>
                    </label>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-xl transition">
                    Annuler
                </button>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-white rounded-xl transition shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-user-plus mr-2"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Roles Chart - Optimisé
const ctxRoles = document.getElementById('rolesChart')?.getContext('2d');
if (ctxRoles) {
    const rolesData = @json($rolesCount ?? []);
    const labels = Object.keys(rolesData).map(r => r.replace('_', ' '));
    const values = Object.values(rolesData);
    const colors = Object.keys(rolesData).map(r => {
        switch(r) {
            case 'super_admin': return '#818cf8';
            case 'admin_reseau': return '#22d3ee';
            case 'admin_service': return '#34d399';
            case 'employe': return '#fbbf24';
            default: return '#94a3b8';
        }
    });
    
    new Chart(ctxRoles, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderColor: '#1e293b',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            animation: { duration: 0 },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// Status Chart - Optimisé
const ctxStatus = document.getElementById('statusChart')?.getContext('2d');
if (ctxStatus) {
    new Chart(ctxStatus, {
        type: 'pie',
        data: {
            labels: ['Actifs', 'Inactifs'],
            datasets: [{
                data: [{{ $activeUsers }}, {{ $inactiveUsers }}],
                backgroundColor: ['#34d399', '#f43f5e'],
                borderColor: '#1e293b',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 0 },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// Services Chart - Optimisé
const ctxServices = document.getElementById('servicesChart')?.getContext('2d');
if (ctxServices) {
    const servicesData = @json($serviceCount ?? []);
    const serviceLabels = Object.keys(servicesData).slice(0, 6);
    const serviceValues = Object.values(servicesData).slice(0, 6);
    const serviceColors = ['#a855f7', '#22d3ee', '#34d399', '#fbbf24', '#f472b6', '#94a3b8'];
    
    new Chart(ctxServices, {
        type: 'bar',
        data: {
            labels: serviceLabels,
            datasets: [{
                label: 'Employés',
                data: serviceValues,
                backgroundColor: serviceColors.slice(0, serviceLabels.length),
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 0 },
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: { 
                        color: '#94a3b8',
                        font: { size: 10 },
                        maxRotation: 45
                    },
                    grid: { display: false }
                },
                y: {
                    ticks: { 
                        color: '#94a3b8',
                        font: { size: 10 }
                    },
                    grid: { color: '#334155' },
                    beginAtZero: true
                }
            }
        }
    });
}

// Modal functions
function closeModal() {
    document.getElementById('create-user-modal').classList.add('hidden');
    document.getElementById('create-user-modal').classList.remove('flex');
}

// Close modal on outside click
document.getElementById('create-user-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

// Filter users
function filterUsers(type) {
    const rows = document.querySelectorAll('#usersTable tbody tr');
    rows.forEach(row => {
        if (type === 'all') {
            row.style.display = '';
        } else {
            const status = row.getAttribute('data-status');
            row.style.display = status === type ? '' : 'none';
        }
    });
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeModal();
});
</script>
@endsection
