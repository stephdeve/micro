@extends('layouts.app')

@section('title', 'Modifier - ' . $employe->name)

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25">
                <i class="fas fa-user-edit text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Modifier un employé</h1>
                <p class="text-slate-400">{{ $employe->name }}</p>
            </div>
        </div>
        <a href="{{ route('admin-service.employes') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <!-- Avatar Card -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 mb-6 flex items-center gap-6">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center text-3xl font-bold text-white shadow-lg">
            {{ strtoupper(substr($employe->name, 0, 2)) }}
        </div>
        <div>
            <h3 class="text-lg font-semibold text-white">{{ $employe->name }}</h3>
            <p class="text-slate-400 text-sm">{{ $employe->email }}</p>
            <div class="flex items-center gap-3 mt-2">
                @foreach($employe->roles as $role)
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30">
                        {{ $role->name }}
                    </span>
                @endforeach
                @if($employe->est_actif)
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center gap-1">
                        <i class="fas fa-circle text-[6px]"></i> Actif
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-slate-600/30 text-slate-400 border border-slate-600/30 flex items-center gap-1">
                        <i class="fas fa-circle text-[6px]"></i> Inactif
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin-service.employes.update', $employe) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Informations personnelles -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-user text-blue-400"></i>
                </div>
                <h2 class="text-lg font-semibold text-white">Informations personnelles</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium text-slate-300">Nom complet <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" id="name" value="{{ $employe->name }}" required
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-slate-300">Email <span class="text-rose-400">*</span></label>
                    <input type="email" name="email" id="email" value="{{ $employe->email }}" required
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                </div>

                <div class="space-y-2">
                    <label for="telephone" class="text-sm font-medium text-slate-300">Téléphone</label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="telephone" id="telephone" value="{{ $employe->telephone }}"
                               class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="fonction" class="text-sm font-medium text-slate-300">Fonction</label>
                    <div class="relative">
                        <i class="fas fa-briefcase absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="fonction" id="fonction" value="{{ $employe->fonction }}"
                               class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"
                               placeholder="ex: Développeur">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sécurité -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-rose-500/20 flex items-center justify-center">
                    <i class="fas fa-lock text-rose-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">Sécurité</h2>
                    <p class="text-xs text-slate-400">Laissez vide pour ne pas modifier le mot de passe</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-slate-300">Nouveau mot de passe</label>
                    <div class="relative">
                        <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password" id="password"
                               class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-slate-300">Confirmer le mot de passe</label>
                    <div class="relative">
                        <i class="fas fa-check-circle absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all"
                               placeholder="••••••••">
                    </div>
                </div>
            </div>
        </div>

        <!-- Rôle et Statut -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <i class="fas fa-shield-alt text-purple-400"></i>
                </div>
                <h2 class="text-lg font-semibold text-white">Rôle et Statut</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="role" class="text-sm font-medium text-slate-300">Rôle <span class="text-rose-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-id-badge absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <select name="role" id="role" required
                                class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all appearance-none">
                            @foreach($roles as $roleName => $label)
                                <option value="{{ $roleName }}" {{ $employe->hasRole($roleName) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"></i>
                    </div>
                </div>

                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="est_actif" value="1" {{ $employe->est_actif ? 'checked' : '' }}
                                   class="peer sr-only">
                            <div class="w-14 h-8 bg-slate-700 peer-checked:bg-emerald-500 rounded-full transition-colors duration-300"></div>
                            <div class="absolute left-1 top-1 w-6 h-6 bg-white rounded-full transition-transform duration-300 peer-checked:translate-x-6 shadow-md"></div>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-slate-300 group-hover:text-white transition-colors block">Compte actif</span>
                            <span class="text-xs text-slate-500 block">L'employé pourra se connecter</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4 pt-4">
            <a href="{{ route('admin-service.employes') }}"
               class="px-6 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white font-medium transition-all border border-slate-700 flex items-center gap-2">
                <i class="fas fa-times"></i> Annuler
            </a>
            <button type="submit"
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium transition-all shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 flex items-center gap-2">
                <i class="fas fa-save"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection
