@extends('layouts.hotspot')

@section('title', 'Guide Connexion WiFi - BHT')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                    <i class="fas fa-wifi text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white">Guide Connexion</h1>
                    <p class="text-xs text-slate-400">WiFi Entreprise BHT</p>
                </div>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <!-- Choix du réseau -->
            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-cyan-400 flex items-center gap-2">
                    <i class="fas fa-1"></i> Choisissez votre réseau
                </h2>
                <div class="space-y-2">
                    <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-tools text-emerald-400 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-white">BHT-Techniciens</div>
                            <div class="text-xs text-slate-400">Pour le personnel technique</div>
                        </div>
                        <i class="fas fa-lock text-emerald-400 text-xs"></i>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-users text-blue-400 text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-medium text-white">BHT-Employes</div>
                            <div class="text-xs text-slate-400">Pour les employés (Hotspot)</div>
                        </div>
                        <i class="fas fa-unlock text-amber-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <!-- Étapes connexion -->
            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-cyan-400 flex items-center gap-2">
                    <i class="fas fa-2"></i> Connexion WiFi Classique
                </h2>
                <div class="bg-slate-800/30 rounded-lg p-4 space-y-3">
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-cyan-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-cyan-400 font-bold">1</span>
                        </div>
                        <p class="text-sm text-slate-300">Ouvrez les paramètres WiFi de votre appareil</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-cyan-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-cyan-400 font-bold">2</span>
                        </div>
                        <p class="text-sm text-slate-300">Sélectionnez <strong class="text-white">BHT-Techniciens</strong></p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-cyan-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-cyan-400 font-bold">3</span>
                        </div>
                        <p class="text-sm text-slate-300">Entrez le mot de passe fourni par l'admin</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-emerald-400 font-bold">✓</span>
                        </div>
                        <p class="text-sm text-slate-300">Connecté ! Vous avez accès à Internet selon les limites de la zone</p>
                    </div>
                </div>
            </div>

            <!-- Hotspot -->
            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-purple-400 flex items-center gap-2">
                    <i class="fas fa-3"></i> Connexion Hotspot (Portail Captif)
                </h2>
                <div class="bg-slate-800/30 rounded-lg p-4 space-y-3">
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-purple-400 font-bold">1</span>
                        </div>
                        <p class="text-sm text-slate-300">Connectez-vous au WiFi <strong class="text-white">BHT-Employes</strong> (sans mot de passe)</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-purple-400 font-bold">2</span>
                        </div>
                        <p class="text-sm text-slate-300">Une page de login s'ouvre automatiquement</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-purple-400 font-bold">3</span>
                        </div>
                        <p class="text-sm text-slate-300">Entrez vos identifiants (fournis par le service IT)</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs text-emerald-400 font-bold">✓</span>
                        </div>
                        <p class="text-sm text-slate-300">Validé ! Vous êtes connecté à Internet</p>
                    </div>
                </div>
            </div>

            <!-- Restrictions -->
            <div class="space-y-3">
                <h2 class="text-sm font-semibold text-amber-400 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i> Restrictions
                </h2>
                <div class="bg-amber-500/5 border border-amber-500/20 rounded-lg p-3">
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-clock text-amber-400 mt-0.5"></i>
                            <span>Accès Internet limité aux horaires de travail (07h00 - 20h00)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-tachometer-alt text-amber-400 mt-0.5"></i>
                            <span>Vitesse limitée selon votre profil (5 Mbps / 2 Mbps)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-database text-amber-400 mt-0.5"></i>
                            <span>Quota mensuel : 50 Go par personne</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-ban text-rose-400 mt-0.5"></i>
                            <span>Sites interdits : réseaux sociaux, streaming, jeux</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Support -->
            <div class="text-center pt-4 border-t border-slate-700">
                <p class="text-xs text-slate-400 mb-2">Problème de connexion ?</p>
                <a href="mailto:support@bht.com" class="text-sm text-cyan-400 hover:text-cyan-300">
                    <i class="fas fa-envelope mr-1"></i> Contacter le support IT
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
