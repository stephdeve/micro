@extends('layouts.app')

@section('title', 'Modifier employé - ' . $user->name)

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    @php
        $currentRole = $user->getRoleNames()->first() ?? 'sans_role';
        $roleColor = match($currentRole) {
            'super_admin' => 'amber',
            'admin_reseau' => 'cyan',
            'admin_service' => 'emerald',
            'employe' => 'indigo',
            default => 'slate'
        };
        $initial = strtoupper(substr($user->name, 0, 1));
    @endphp

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="p-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center justify-center group">
                <i class="fas fa-arrow-left text-{{ $roleColor }}-400 group-hover:text-{{ $roleColor }}-300"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-{{ $roleColor }}-400 via-purple-400 to-pink-500 bg-clip-text text-transparent">
                    <i class="fas fa-user-edit mr-3"></i>Modifier l'employé
                </h1>
                <p class="text-slate-400 mt-1">
                    Mise à jour des informations de <span class="text-white font-medium">{{ $user->name }}</span>
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl">
                <span class="w-2 h-2 rounded-full {{ $user->est_actif ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
                <span class="text-sm text-slate-300">{{ $user->est_actif ? 'Compte actif' : 'Compte inactif' }}</span>
            </div>
            <span class="px-3 py-1.5 bg-{{ $roleColor }}-500/20 text-{{ $roleColor }}-400 rounded-lg text-sm capitalize">
                {{ str_replace('_', ' ', $currentRole) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Column - User Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 sticky top-6">
                <!-- Avatar -->
                <div class="text-center mb-6">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-{{ $roleColor }}-500 to-{{ $roleColor }}-600 rounded-2xl flex items-center justify-center text-4xl font-bold text-white shadow-lg shadow-{{ $roleColor }}-500/25 mb-4">
                        {{ $initial }}
                    </div>
                    <h3 class="text-xl font-bold text-white">{{ $user->name }}</h3>
                    <p class="text-slate-400 text-sm">{{ $user->email }}</p>
                    <p class="text-{{ $roleColor }}-400 text-sm mt-2">{{ $user->fonction ?? 'Fonction non définie' }}</p>
                </div>

                <!-- Quick Stats -->
                <div class="space-y-3 mb-6">
                    <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                        <span class="text-slate-400 text-sm">Membre depuis</span>
                        <span class="text-white text-sm">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                        <span class="text-slate-400 text-sm">Dernière modif</span>
                        <span class="text-white text-sm">{{ $user->updated_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                        <span class="text-slate-400 text-sm">Service</span>
                        <span class="text-white text-sm">{{ $user->service?->nom ?? 'Non assigné' }}</span>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="border-t border-slate-700 pt-6">
                    <h4 class="text-rose-400 text-sm font-medium mb-3 flex items-center gap-2">
                        <i class="fas fa-exclamation-triangle"></i>
                        Zone de danger
                    </h4>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet employé ? Cette action est irréversible.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-3 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 text-rose-400 rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fas fa-trash-alt"></i>
                            <span>Supprimer cet employé</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column - Edit Form -->
        <div class="lg:col-span-2">
            <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
                <!-- Form Header -->
                <div class="p-6 border-b border-slate-700">
                    <h3 class="text-lg font-semibold flex items-center gap-2">
                        <i class="fas fa-pen-fancy text-{{ $roleColor }}-400"></i>
                        Informations personnelles
                    </h3>
                    <p class="text-slate-400 text-sm mt-1">Modifiez les informations de l'employé ci-dessous.</p>
                </div>

                <!-- Error Messages -->
                @if($errors->any())
                    <div class="mx-6 mt-6 p-4 bg-rose-500/10 border border-rose-500/30 rounded-xl">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-exclamation-circle text-rose-400 mt-0.5"></i>
                            <div>
                                <h4 class="text-rose-400 font-medium text-sm mb-2">Erreurs de validation</h4>
                                <ul class="text-rose-300/80 text-sm space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li class="flex items-center gap-2">
                                            <span class="w-1 h-1 bg-rose-400 rounded-full"></span>
                                            {{ $error }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('users.update', $user) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Identity Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-2">Nom complet *</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input type="text" name="name" required 
                                    value="{{ old('name', $user->name) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-{{ $roleColor }}-500 focus:ring-1 focus:ring-{{ $roleColor }}-500 transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-400 mb-2">Email *</label>
                            <div class="relative">
                                <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input type="email" name="email" required 
                                    value="{{ old('email', $user->email) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-{{ $roleColor }}-500 focus:ring-1 focus:ring-{{ $roleColor }}-500 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-2">Téléphone</label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input type="text" name="telephone" 
                                    value="{{ old('telephone', $user->telephone) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-{{ $roleColor }}-500 focus:ring-1 focus:ring-{{ $roleColor }}-500 transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-slate-400 mb-2">Fonction</label>
                            <div class="relative">
                                <i class="fas fa-briefcase absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <input type="text" name="fonction" 
                                    value="{{ old('fonction', $user->fonction) }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-{{ $roleColor }}-500 focus:ring-1 focus:ring-{{ $roleColor }}-500 transition">
                            </div>
                        </div>
                    </div>

                    <!-- Password Section -->
                    <div class="p-4 bg-slate-900/50 rounded-xl border border-slate-700">
                        <h4 class="text-sm font-medium text-slate-300 mb-4 flex items-center gap-2">
                            <i class="fas fa-lock text-{{ $roleColor }}-400"></i>
                            Modifier le mot de passe
                            <span class="text-xs text-slate-500">(laisser vide pour conserver l'actuel)</span>
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Nouveau mot de passe</label>
                                <div class="relative">
                                    <i class="fas fa-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                    <input type="password" name="password"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-{{ $roleColor }}-500 focus:ring-1 focus:ring-{{ $roleColor }}-500 transition">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm text-slate-400 mb-2">Confirmer le mot de passe</label>
                                <div class="relative">
                                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                    <input type="password" name="password_confirmation"
                                        class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-500 focus:outline-none focus:border-{{ $roleColor }}-500 focus:ring-1 focus:ring-{{ $roleColor }}-500 transition">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Status Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm text-slate-400 mb-2">Rôle *</label>
                            <div class="relative">
                                <i class="fas fa-user-shield absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                <select name="role" required 
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl py-3 pl-10 pr-4 text-white focus:outline-none focus:border-{{ $roleColor }}-500 focus:ring-1 focus:ring-{{ $roleColor }}-500 transition appearance-none">
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" {{ (old('role', $currentRole) == $role) ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
                            </div>
                        </div>

                        <div class="flex items-center">
                            <div class="w-full">
                                <label class="block text-sm text-slate-400 mb-3">Statut du compte</label>
                                <label class="flex items-center gap-3 p-3 bg-slate-900 rounded-xl cursor-pointer border border-slate-700 hover:border-emerald-500/30 transition">
                                    <div class="relative">
                                        <input type="checkbox" name="est_actif" value="1" 
                                            {{ old('est_actif', $user->est_actif) ? 'checked' : '' }} 
                                            class="sr-only peer">
                                        <div class="w-14 h-7 bg-slate-700 rounded-full peer peer-checked:bg-emerald-500 transition-colors"></div>
                                        <div class="absolute left-1 top-1 w-5 h-5 bg-white rounded-full peer-checked:translate-x-7 transition-transform shadow-lg"></div>
                                    </div>
                                    <span class="text-slate-300">Compte actif</span>
                                    <span class="ml-auto text-xs {{ $user->est_actif ? 'text-emerald-400' : 'text-rose-400' }}">
                                        {{ $user->est_actif ? 'Activé' : 'Désactivé' }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-700">
                        <a href="{{ route('users.index') }}" 
                            class="px-6 py-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white rounded-xl transition flex items-center justify-center gap-2">
                            <i class="fas fa-times"></i>
                            <span>Annuler</span>
                        </a>
                        <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-{{ $roleColor }}-500 to-purple-600 hover:from-{{ $roleColor }}-400 hover:to-purple-500 text-white rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-{{ $roleColor }}-500/25">
                            <i class="fas fa-save"></i>
                            <span>Enregistrer les modifications</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
