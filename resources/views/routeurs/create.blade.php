@extends('layouts.app')

@section('title', 'Nouveau Routeur')

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                <i class="fas fa-plus-circle mr-3"></i>Nouveau Routeur
            </h1>
            <p class="text-slate-400 mt-1">Ajouter un nouveau routeur MikroTik a l'infrastructure</p>
        </div>
        <a href="{{ route('routeurs.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>

    <!-- Form -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 max-w-3xl">
        <form action="{{ route('routeurs.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nom -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Nom du routeur <span class="text-rose-400">*</span></label>
                    <input type="text" name="nom" required 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition"
                           placeholder="Ex: Routeur-Principal-Siege">
                </div>

                <!-- Adresse IP -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Adresse IP <span class="text-rose-400">*</span></label>
                    <input type="text" name="adresse_ip" required 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition font-mono"
                           placeholder="Ex: 192.168.1.1">
                </div>

                <!-- Modèle -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Modele</label>
                    <input type="text" name="modele" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition"
                           placeholder="Ex: RB3011UiAS-RM">
                </div>

                <!-- Version ROS -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Version RouterOS</label>
                    <input type="text" name="version_ros" 
                           class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition"
                           placeholder="Ex: 7.12.1">
                </div>

                <!-- Statut -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Statut <span class="text-rose-400">*</span></label>
                    <select name="statut" required 
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition">
                        <option value="en_ligne">En ligne</option>
                        <option value="hors_ligne">Hors ligne</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-300 mb-2">Description (optionnel)</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-cyan-500/50 focus:ring-2 focus:ring-cyan-500/20 transition resize-none"
                              placeholder="Notes ou informations complementaires..."></textarea>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4 mt-8 pt-6 border-t border-slate-700">
                <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center justify-center gap-2 shadow-lg shadow-cyan-500/25">
                    <i class="fas fa-save"></i>
                    <span>Enregistrer</span>
                </button>
                <a href="{{ route('routeurs.index') }}" class="px-6 py-3 bg-slate-700 hover:bg-slate-600 rounded-xl transition flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    <span>Annuler</span>
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
