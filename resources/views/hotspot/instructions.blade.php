<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment se connecter au WiFi Entreprise</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-900 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 py-12">
        {{-- Header --}}
        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg shadow-purple-500/25">
                <i class="fas fa-wifi text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Guide de connexion WiFi</h1>
            <p class="text-slate-400">Réseau Entreprise - Hotspot</p>
        </div>

        {{-- Étapes --}}
        <div class="space-y-6">
            {{-- Étape 1 --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-purple-400 font-bold">1</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Connectez-vous au réseau WiFi</h3>
                        <p class="text-slate-400 mb-3">Sur votre appareil (PC, téléphone, tablette), recherchez et sélectionnez le réseau :</p>
                        <div class="bg-slate-900 rounded-lg p-3 inline-flex items-center gap-2">
                            <i class="fas fa-wifi text-emerald-400"></i>
                            <span class="text-white font-medium">{{ $routeur->nom ?? 'WiFi-Entreprise' }}</span>
                            <span class="text-xs bg-slate-700 px-2 py-1 rounded text-slate-300">ou Hotspot</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Étape 2 --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-purple-400 font-bold">2</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Attendez la redirection automatique</h3>
                        <p class="text-slate-400 mb-3">Une fois connecté au WiFi, ouvrez votre navigateur et essayez d'accéder à n'importe quel site web. Vous serez automatiquement redirigé vers la page de connexion.</p>
                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-3">
                            <p class="text-amber-400 text-sm">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Si la redirection ne fonctionne pas, essayez d'accéder manuellement à : <code class="bg-slate-900 px-2 py-1 rounded text-emerald-400">http://neverssl.com</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Étape 3 --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-purple-400 font-bold">3</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Entrez vos identifiants</h3>
                        <p class="text-slate-400 mb-3">Sur la page de connexion qui s'affiche, entrez :</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-slate-900 rounded-lg p-3">
                                <p class="text-xs text-slate-500 mb-1">Nom d'utilisateur</p>
                                <p class="text-white font-medium">Votre code employé</p>
                                <p class="text-xs text-slate-500">(ex: EMP001)</p>
                            </div>
                            <div class="bg-slate-900 rounded-lg p-3">
                                <p class="text-xs text-slate-500 mb-1">Mot de passe</p>
                                <p class="text-white font-medium">Votre mot de passe</p>
                                <p class="text-xs text-slate-500">(fourni par l'admin)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Étape 4 --}}
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 bg-purple-500/20 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-purple-400 font-bold">4</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Accès Internet</h3>
                        <p class="text-slate-400">Après validation, vous avez accès à Internet ! Vous pouvez naviguer normalement.</p>
                        <div class="mt-3 flex items-center gap-2 text-emerald-400">
                            <i class="fas fa-check-circle"></i>
                            <span>Connexion réussie</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Page de démo --}}
        <div class="mt-10 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 rounded-xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <i class="fas fa-desktop text-indigo-400 text-xl"></i>
                <h3 class="text-lg font-semibold text-white">Tester la page de connexion</h3>
            </div>
            <p class="text-slate-400 mb-4">Vous pouvez voir à quoi ressemble la page de connexion ici :</p>
            <a href="{{ route('hotspot.login', $routeur) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-600 hover:to-purple-600 text-white font-medium rounded-xl transition-all">
                <i class="fas fa-external-link-alt"></i>
                Voir la page de connexion
            </a>
        </div>

        {{-- Assistance --}}
        <div class="mt-8 text-center">
            <p class="text-slate-500 text-sm">
                <i class="fas fa-life-ring mr-2"></i>
                Problème de connexion ? Contactez le service informatique
            </p>
        </div>
    </div>
</body>
</html>
