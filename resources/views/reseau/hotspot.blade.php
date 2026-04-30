@extends('layouts.app')

@section('title', 'Hotspot - ' . $routeur->nom)

@section('content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center shadow-lg shadow-purple-500/25">
                <i class="fas fa-wifi text-xl text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Portail Captif Hotspot</h1>
                <p class="text-sm text-slate-400">{{ $routeur->nom }} &middot; {{ $routeur->adresse_ip }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('routeurs.index') }}" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-medium transition-all border border-slate-700 inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button type="button" onclick="openModal('addUser')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white text-sm font-medium transition-all shadow-lg shadow-purple-500/25 inline-flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Nouvel Utilisateur
            </button>
            <button type="button" onclick="openModal('vouchers')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white text-sm font-medium transition-all shadow-lg shadow-emerald-500/25 inline-flex items-center gap-2">
                <i class="fas fa-ticket-alt"></i> Vouchers
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Utilisateurs Total</p>
                    <p class="text-2xl font-bold text-white">{{ $users->total() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <i class="fas fa-users text-blue-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Actifs</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ $activeUsers }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">En Ligne</p>
                    <p class="text-2xl font-bold text-cyan-400">{{ $onlineUsers }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center">
                    <i class="fas fa-wifi text-cyan-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Profils</p>
                    <p class="text-2xl font-bold text-purple-400">{{ $profiles->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                    <i class="fas fa-id-badge text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-slate-700">
        <div class="flex gap-2">
            <button onclick="switchTab('users')" id="tab-users" class="px-4 py-2 text-sm font-medium text-white border-b-2 border-purple-500">
                <i class="fas fa-users mr-2"></i>Utilisateurs
            </button>
            <button onclick="switchTab('profiles')" id="tab-profiles" class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white border-b-2 border-transparent">
                <i class="fas fa-id-badge mr-2"></i>Profils
            </button>
            <button onclick="switchTab('online')" id="tab-online" class="px-4 py-2 text-sm font-medium text-slate-400 hover:text-white border-b-2 border-transparent">
                <i class="fas fa-wifi mr-2"></i>En Ligne
            </button>
        </div>
    </div>

    {{-- Users Tab --}}
    <div id="content-users" class="space-y-4">
        {{-- Search --}}
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                <input type="text" id="searchUsers" placeholder="Rechercher un utilisateur..." 
                       class="w-full pl-10 pr-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white placeholder-slate-500 focus:outline-none focus:border-purple-500">
            </div>
            <button onclick="refreshActiveUsers()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-slate-300 transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Actualiser
            </button>
        </div>

        {{-- Users Table --}}
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-900/50">
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Utilisateur</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Profil</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Limites</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Validité</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Statut</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-800 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-700 flex items-center justify-center">
                                            <i class="fas fa-user text-slate-400 text-xs"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $user->username }}</p>
                                            @if($user->nom_complet)
                                                <p class="text-xs text-slate-400">{{ $user->nom_complet }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs 
                                        {{ $user->type === 'voucher' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                                        {{ $user->type === 'employe' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : '' }}
                                        {{ $user->type === 'invite' ? 'bg-pink-500/10 text-pink-400 border border-pink-500/20' : '' }}
                                        {{ $user->type === 'permanent' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}">
                                        {{ ucfirst($user->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-300">{{ $user->profile?->nom ?? 'Default' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-400">
                                    @if($user->data_limit)
                                        <div><i class="fas fa-database mr-1"></i>{{ $user->dataLimitFormatted() }}</div>
                                    @endif
                                    @if($user->time_limit)
                                        <div><i class="fas fa-clock mr-1"></i>{{ $user->time_limit }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-400">
                                    @if($user->valid_until)
                                        <span class="{{ now()->gt($user->valid_until) ? 'text-rose-400' : 'text-emerald-400' }}">
                                            {{ $user->valid_until->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    {!! $user->statusBadge() !!}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button onclick="editUser({{ $user->id }})" class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors flex items-center justify-center" title="Modifier">
                                            <i class="fas fa-edit text-xs"></i>
                                        </button>
                                        <form action="{{ route('admin-reseau.hotspot.users.toggle', [$routeur, $user]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-8 h-8 rounded-lg {{ $user->disabled ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-amber-500/10 text-amber-400 hover:bg-amber-500/20' }} transition-colors flex items-center justify-center" title="{{ $user->disabled ? 'Activer' : 'Désactiver' }}">
                                                <i class="fas fa-power-off text-xs"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin-reseau.hotspot.users.destroy', [$routeur, $user]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 transition-colors flex items-center justify-center" title="Supprimer">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-slate-500">
                                    <i class="fas fa-users text-3xl mb-2"></i>
                                    <p>Aucun utilisateur Hotspot</p>
                                    <button onclick="openModal('addUser')" class="mt-2 text-purple-400 hover:text-purple-300">
                                        <i class="fas fa-plus mr-1"></i>Créer le premier utilisateur
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="px-4 py-3 border-t border-slate-700">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Profiles Tab --}}
    <div id="content-profiles" class="hidden space-y-4">
        <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-white">Profils Hotspot</h3>
            <button onclick="openModal('addProfile')" class="px-4 py-2 rounded-lg bg-purple-500 hover:bg-purple-600 text-white text-sm font-medium transition-colors">
                <i class="fas fa-plus mr-2"></i>Nouveau Profil
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($profiles as $profile)
                <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-white">{{ $profile->nom }}</h4>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $profile->active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                            {{ $profile->active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                    <div class="space-y-2 text-sm text-slate-400">
                        <div class="flex justify-between">
                            <span>Utilisateurs partagés:</span>
                            <span class="text-white">{{ $profile->shared_users }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Bande passante:</span>
                            <span class="text-white">{{ $profile->rateLimitFormatted() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Session timeout:</span>
                            <span class="text-white">{{ $profile->sessionTimeoutFormatted() }}</span>
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-700 flex justify-between text-xs">
                        <span class="text-slate-500">{{ $profile->users()->count() }} utilisateurs</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-slate-500">
                    <i class="fas fa-id-badge text-3xl mb-2"></i>
                    <p>Aucun profil configuré</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Online Tab --}}
    <div id="content-online" class="hidden space-y-4">
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div id="online-users-container">
                <p class="text-center text-slate-500 py-4">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Chargement des utilisateurs en ligne...
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Add User --}}
<div id="modal-addUser" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white"><i class="fas fa-user-plus mr-2 text-purple-400"></i>Nouvel Utilisateur</h3>
            <button onclick="closeModal('addUser')" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('admin-reseau.hotspot.users.store', $routeur) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Nom d'utilisateur *</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Mot de passe</label>
                    <input type="text" name="password" placeholder="Auto-généré si vide" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Type</label>
                    <select name="type" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                        <option value="employe">Employé</option>
                        <option value="invite">Invité</option>
                        <option value="permanent">Permanent</option>
                        <option value="voucher">Voucher</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Profil</label>
                    <select name="profile_id" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                        <option value="">Default</option>
                        @foreach($profiles as $profile)
                            <option value="{{ $profile->id }}">{{ $profile->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Nom complet</label>
                <input type="text" name="nom_complet" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Email</label>
                    <input type="email" name="email" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Téléphone</label>
                    <input type="text" name="telephone" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Adresse MAC (optionnel)</label>
                <input type="text" name="mac_address" placeholder="AA:BB:CC:DD:EE:FF" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Limite de données (Mo)</label>
                    <input type="number" name="data_limit" min="0" placeholder="Illimité" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Limite de temps</label>
                    <input type="text" name="time_limit" placeholder="ex: 1h, 1d" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Valide depuis</label>
                    <input type="date" name="valid_from" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Valide jusqu'au</label>
                    <input type="date" name="valid_until" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Commentaire</label>
                <textarea name="commentaire" rows="2" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('addUser')" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition-colors">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white transition-all">
                    <i class="fas fa-save mr-2"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Vouchers --}}
<div id="modal-vouchers" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-lg w-full">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white"><i class="fas fa-ticket-alt mr-2 text-emerald-400"></i>Générer des Vouchers</h3>
            <button onclick="closeModal('vouchers')" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('admin-reseau.hotspot.vouchers', $routeur) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Quantité *</label>
                    <input type="number" name="quantity" required min="1" max="100" value="10" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Préfixe</label>
                    <input type="text" name="prefix" value="WIFI" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-emerald-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Profil</label>
                <select name="profile_id" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-emerald-500 focus:outline-none">
                    <option value="">Default</option>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile->id }}">{{ $profile->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Limite de données (Mo)</label>
                    <input type="number" name="data_limit" min="0" placeholder="Illimité" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Limite de temps</label>
                    <input type="text" name="time_limit" placeholder="ex: 1h, 1d" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-emerald-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Valide jusqu'au</label>
                <input type="date" name="valid_until" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-emerald-500 focus:outline-none">
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('vouchers')" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition-colors">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white transition-all">
                    <i class="fas fa-magic mr-2"></i>Générer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Add Profile --}}
<div id="modal-addProfile" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-lg w-full">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="text-lg font-bold text-white"><i class="fas fa-id-badge mr-2 text-purple-400"></i>Nouveau Profil</h3>
            <button onclick="closeModal('addProfile')" class="text-slate-400 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('admin-reseau.hotspot.profiles.store', $routeur) }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm text-slate-400 mb-1">Nom du profil *</label>
                <input type="text" name="nom" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Utilisateurs partagés</label>
                    <input type="number" name="shared_users" min="1" max="50" value="1" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Rate limit</label>
                    <input type="text" name="rate_limit" placeholder="ex: 2M/4M" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Session timeout</label>
                    <input type="text" name="session_timeout" placeholder="ex: 4h, 1d" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm text-slate-400 mb-1">Idle timeout</label>
                    <input type="text" name="idle_timeout" value="00:05:00" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Commentaire</label>
                <textarea name="commentaire" rows="2" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white focus:border-purple-500 focus:outline-none"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('addProfile')" class="px-4 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white transition-colors">Annuler</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white transition-all">
                    <i class="fas fa-save mr-2"></i>Créer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(modalId) {
    document.getElementById('modal-' + modalId).classList.remove('hidden');
    document.getElementById('modal-' + modalId).classList.add('flex');
}

function closeModal(modalId) {
    document.getElementById('modal-' + modalId).classList.add('hidden');
    document.getElementById('modal-' + modalId).classList.remove('flex');
}

function switchTab(tab) {
    // Hide all content
    document.getElementById('content-users').classList.add('hidden');
    document.getElementById('content-profiles').classList.add('hidden');
    document.getElementById('content-online').classList.add('hidden');
    
    // Reset all tabs
    document.getElementById('tab-users').classList.remove('text-white', 'border-purple-500');
    document.getElementById('tab-users').classList.add('text-slate-400', 'border-transparent');
    document.getElementById('tab-profiles').classList.remove('text-white', 'border-purple-500');
    document.getElementById('tab-profiles').classList.add('text-slate-400', 'border-transparent');
    document.getElementById('tab-online').classList.remove('text-white', 'border-purple-500');
    document.getElementById('tab-online').classList.add('text-slate-400', 'border-transparent');
    
    // Show selected content and tab
    document.getElementById('content-' + tab).classList.remove('hidden');
    document.getElementById('tab-' + tab).classList.remove('text-slate-400', 'border-transparent');
    document.getElementById('tab-' + tab).classList.add('text-white', 'border-purple-500');
    
    if (tab === 'online') {
        refreshActiveUsers();
    }
}

function refreshActiveUsers() {
    const container = document.getElementById('online-users-container');
    container.innerHTML = '<p class="text-center text-slate-500 py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</p>';
    
    fetch('{{ route('admin-reseau.hotspot.active-users', $routeur) }}')
        .then(r => r.json())
        .then(data => {
            if (data.users.length === 0) {
                container.innerHTML = '<p class="text-center text-slate-500 py-8"><i class="fas fa-wifi text-3xl mb-2"></i><br>Aucun utilisateur connecté</p>';
                return;
            }
            
            let html = '<div class="overflow-x-auto"><table class="w-full"><thead><tr class="border-b border-slate-700"><th class="px-4 py-2 text-left text-xs text-slate-400">Utilisateur</th><th class="px-4 py-2 text-left text-xs text-slate-400">IP</th><th class="px-4 py-2 text-left text-xs text-slate-400">MAC</th><th class="px-4 py-2 text-left text-xs text-slate-400">Uptime</th><th class="px-4 py-2 text-left text-xs text-slate-400">Données</th><th class="px-4 py-2 text-right text-xs text-slate-400">Action</th></tr></thead><tbody class="divide-y divide-slate-700">';
            
            data.users.forEach(user => {
                const bytesIn = formatBytes(user.bytes_in || 0);
                const bytesOut = formatBytes(user.bytes_out || 0);
                html += `<tr class="hover:bg-slate-800">
                    <td class="px-4 py-2 text-sm text-white">${user.user}</td>
                    <td class="px-4 py-2 text-sm text-slate-400">${user.address}</td>
                    <td class="px-4 py-2 text-sm text-slate-400 font-mono">${user.mac_address}</td>
                    <td class="px-4 py-2 text-sm text-slate-400">${user.uptime}</td>
                    <td class="px-4 py-2 text-sm text-slate-400">↓${bytesIn} ↑${bytesOut}</td>
                    <td class="px-4 py-2 text-right">
                        <button onclick="disconnectUser('${user.user}')" class="px-3 py-1 rounded bg-rose-500/10 text-rose-400 hover:bg-rose-500/20 text-xs transition-colors">
                            <i class="fas fa-sign-out-alt mr-1"></i>Déconnecter
                        </button>
                    </td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
        })
        .catch(e => {
            container.innerHTML = '<p class="text-center text-rose-400 py-4"><i class="fas fa-exclamation-circle mr-2"></i>Erreur de chargement</p>';
        });
}

function disconnectUser(username) {
    if (!confirm('Déconnecter ' + username + ' ?')) return;
    
    fetch('{{ route('admin-reseau.hotspot.disconnect', $routeur) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ username: username })
    }).then(() => refreshActiveUsers());
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Search functionality
document.getElementById('searchUsers')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Close modals on outside click
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('fixed')) {
        e.target.classList.add('hidden');
        e.target.classList.remove('flex');
    }
});
</script>
@endsection
