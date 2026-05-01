@extends('layouts.hotspot')

@section('title', 'Connexion WiFi - Portail Captif')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-slate-950">
    <div class="w-full max-w-md">
        {{-- Card principale --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden">
            {{-- Header avec gradient --}}
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-8 text-center">
                <div class="w-20 h-20 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                    <i class="fas fa-wifi text-3xl text-white"></i>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">WiFi {{ $routeur->nom ?? 'Entreprise' }}</h1>
                <p class="text-white/80 text-sm">Portail d'accès Internet</p>
            </div>

            {{-- Formulaire de connexion --}}
            <div class="p-8">
                @if(session('error'))
                    <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-rose-400 mt-0.5"></i>
                        <div class="text-sm text-rose-400">{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-start gap-3">
                        <i class="fas fa-check-circle text-emerald-400 mt-0.5"></i>
                        <div class="text-sm text-emerald-400">{{ session('success') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('hotspot.login.submit', $routeur) }}" class="space-y-5">
                    @csrf

                    {{-- Username --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Nom d'utilisateur</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-slate-500"></i>
                            </div>
                            <input type="text" 
                                   name="username" 
                                   required 
                                   autofocus
                                   class="w-full pl-11 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all"
                                   placeholder="Entrez votre identifiant"
                                   value="{{ old('username') }}">
                        </div>
                        @error('username')
                            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Mot de passe</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-slate-500"></i>
                            </div>
                            <input type="password" 
                                   name="password" 
                                   required
                                   class="w-full pl-11 pr-12 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all"
                                   placeholder="Entrez votre mot de passe"
                                   id="passwordInput">
                            <button type="button" 
                                    onclick="togglePassword()"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-300 transition">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember/Stay connected --}}
                    <div class="flex items-center gap-2">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember"
                               class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-purple-500 focus:ring-purple-500/20">
                        <label for="remember" class="text-sm text-slate-400">Rester connecté sur cet appareil</label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" 
                            class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-semibold rounded-xl transition-all transform hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-purple-500/25 flex items-center justify-center gap-2">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Se connecter</span>
                    </button>
                </form>

                {{-- Info section --}}
                <div class="mt-8 pt-6 border-t border-slate-800">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 bg-slate-800 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-info-circle text-cyan-400"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-white mb-1">Comment se connecter ?</h3>
                            <p class="text-xs text-slate-400 leading-relaxed">
                                @if($routeur ?? false)
                                    <strong>Employés :</strong> Utilisez vos identifiants fournis par l'administration.<br>
                                    <strong>Visiteurs :</strong> Demandez un voucher à l'accueil.
                                @else
                                    Entrez vos identifiants pour accéder à Internet. En cas de problème, contactez l'administrateur réseau.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center mt-6">
            <p class="text-xs text-slate-500">
                © {{ date('Y') }} {{ config('app.name', 'Micro') }} - Tous droits réservés
            </p>
            <p class="text-xs text-slate-600 mt-1">
                Connexion sécurisée via routeur {{ $routeur->nom ?? 'MikroTik' }}
            </p>
        </div>
    </div>
</div>

{{-- Script pour toggle password --}}
<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('toggleIcon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
