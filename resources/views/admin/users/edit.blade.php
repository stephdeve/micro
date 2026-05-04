@extends('layouts.app')

@section('title', 'Modifier Administrateur - SuperAdmin')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.users.index') }}" class="w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-all">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Modifier l'Administrateur</h1>
            <p class="text-sm text-slate-400">{{ $user->name }} - {{ $user->email }}</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        @if($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/20 rounded-xl p-4">
            <div class="flex items-center gap-2 text-rose-400 mb-2">
                <i class="fas fa-exclamation-circle"></i>
                <span class="font-medium">Erreurs de validation</span>
            </div>
            <ul class="text-sm text-rose-400 space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Type d'admin -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <label class="block text-sm font-medium text-white mb-3">Type d'administrateur</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative">
                    <input type="radio" name="role" value="admin_reseau" class="peer sr-only" {{ $user->hasRole('admin_reseau') ? 'checked' : '' }}>
                    <div class="p-4 rounded-xl bg-slate-900/50 border-2 border-slate-700 peer-checked:border-cyan-500 peer-checked:bg-cyan-500/10 cursor-pointer transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                                <i class="fas fa-network-wired text-cyan-400"></i>
                            </div>
                            <div>
                                <p class="font-medium text-white">Admin Réseau</p>
                                <p class="text-xs text-slate-400">Gère les routeurs et le WiFi</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-cyan-500 text-white flex items-center justify-center text-xs opacity-0 peer-checked:opacity-100 transition-opacity">
                        <i class="fas fa-check"></i>
                    </div>
                </label>

                <label class="relative">
                    <input type="radio" name="role" value="admin_service" class="peer sr-only" {{ $user->hasRole('admin_service') ? 'checked' : '' }}>
                    <div class="p-4 rounded-xl bg-slate-900/50 border-2 border-slate-700 peer-checked:border-purple-500 peer-checked:bg-purple-500/10 cursor-pointer transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                <i class="fas fa-users-cog text-purple-400"></i>
                            </div>
                            <div>
                                <p class="font-medium text-white">Admin Service</p>
                                <p class="text-xs text-slate-400">Gère les employés et le service</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute top-2 right-2 w-5 h-5 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs opacity-0 peer-checked:opacity-100 transition-opacity">
                        <i class="fas fa-check"></i>
                    </div>
                </label>
            </div>
        </div>

        <!-- Informations -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4 space-y-4">
            <h3 class="text-sm font-medium text-white flex items-center gap-2">
                <i class="fas fa-user"></i> Informations personnelles
            </h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 bg-slate-900/50 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>

                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 bg-slate-900/50 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>

                <div class="col-span-2">
                    <label class="block text-xs text-slate-400 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', $user->telephone) }}" class="w-full px-3 py-2 bg-slate-900/50 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Mot de passe (optionnel) -->
        <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-medium text-amber-400 flex items-center gap-2">
                    <i class="fas fa-lock"></i> Nouveau mot de passe (optionnel)
                </h3>
                <button type="button" onclick="generatePassword()" class="text-xs text-amber-400 hover:text-amber-300 transition-colors">
                    <i class="fas fa-magic mr-1"></i> Générer
                </button>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Mot de passe (laisser vide pour ne pas changer)</label>
                <div class="flex gap-2">
                    <input type="password" name="password" id="password" class="flex-1 px-3 py-2 bg-slate-900/50 border border-slate-700 rounded-lg text-white text-sm font-mono focus:border-amber-500 focus:outline-none" placeholder="Min. 8 caractères, majuscule, chiffre, symbole">
                    <button type="button" onclick="togglePassword()" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-all border border-slate-700">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mt-1">Doit contenir au moins 8 caractères, une majuscule, un chiffre et un symbole</p>
            </div>

            <div class="flex items-center gap-2 p-3 bg-slate-800/30 rounded-lg">
                <input type="checkbox" name="est_actif" id="est_actif" value="1" {{ $user->est_actif ? 'checked' : '' }} class="rounded bg-slate-800 border-slate-700 text-cyan-500">
                <label for="est_actif" class="text-sm text-slate-300">Compte actif</label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-medium transition-all border border-slate-700">
                Annuler
            </a>
            <button type="submit" class="flex-1 px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white text-sm font-medium transition-all shadow-lg shadow-cyan-500/25">
                <i class="fas fa-save mr-2"></i> Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<script>
    function generatePassword() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let password = '';
        
        password += 'A'; // majuscule
        password += '1'; // chiffre
        password += '!'; // symbole
        
        for (let i = 0; i < 9; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        password = password.split('').sort(() => Math.random() - 0.5).join('');
        
        const input = document.getElementById('password');
        input.value = password;
        input.type = 'text';
        document.getElementById('toggleIcon').className = 'fas fa-eye-slash';
    }

    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
</script>
@endsection
