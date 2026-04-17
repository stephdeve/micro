@extends('layouts.app')

@section('title', 'Ajouter un employé')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                <i class="fas fa-user-plus text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Ajouter un employé</h1>
                <p class="text-slate-400">Service : {{ $service->nom ?? '—' }}</p>
            </div>
        </div>
        <a href="{{ route('admin-service.employes') }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors text-sm">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('admin-service.employes.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Informations personnelles -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <i class="fas fa-user text-blue-400"></i>
                </div>
                <h2 class="text-lg font-semibold text-white">Informations personnelles</h2>
                <span class="ml-auto text-xs text-slate-500">* Champs obligatoires</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium text-slate-300">Nom complet <span class="text-rose-400">*</span></label>
                    <input type="text" name="name" id="name" required
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"
                           placeholder="ex: Jean Dupont">
                </div>

                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-slate-300">Email <span class="text-rose-400">*</span></label>
                    <input type="email" name="email" id="email" required
                           class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"
                           placeholder="ex: jean.dupont@bht.com">
                </div>

                <div class="space-y-2">
                    <label for="telephone" class="text-sm font-medium text-slate-300">Téléphone</label>
                    <div class="relative">
                        <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="telephone" id="telephone"
                               class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition-all"
                               placeholder="ex: +33 6 12 34 56 78">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="fonction" class="text-sm font-medium text-slate-300">Fonction</label>
                    <div class="relative">
                        <i class="fas fa-briefcase absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="fonction" id="fonction"
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
                <h2 class="text-lg font-semibold text-white">Sécurité</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium text-slate-300">Mot de passe <span class="text-rose-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password" id="password" required
                               class="w-full pl-11 pr-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 transition-all"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium text-slate-300">Confirmer <span class="text-rose-400">*</span></label>
                    <div class="relative">
                        <i class="fas fa-check-circle absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
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
                            <option value="employe">Employé</option>
                            <option value="admin_service">Admin Service</option>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"></i>
                    </div>
                </div>

                <div class="flex items-end pb-3">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="est_actif" value="1" checked
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
                    class="px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-medium transition-all shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 flex items-center gap-2">
                <i class="fas fa-plus"></i> Créer l'employé
            </button>
        </div>
    </form>
</div>
@endsection
