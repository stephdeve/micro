@extends('layouts.app')

@section('title', 'Sélectionner un Routeur - Hotspot')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white flex items-center gap-3">
            <i class="fas fa-wifi text-purple-400"></i>
            Hotspot - Sélectionner un Routeur
        </h1>
        <p class="text-slate-400 mt-2">Choisissez un routeur pour gérer son portail captif Hotspot</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($routeurs as $routeur)
        <a href="{{ route('admin-reseau.hotspot', $routeur) }}" class="block bg-slate-800/50 border border-slate-700 rounded-xl p-4 hover:border-purple-500/50 transition group">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-router text-purple-400"></i>
                </div>
                <div>
                    <h3 class="font-medium text-white group-hover:text-purple-400 transition">{{ $routeur->nom }}</h3>
                    <p class="text-xs text-slate-400">{{ $routeur->adresse_ip }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="px-2 py-1 rounded {{ $routeur->statut === 'en_ligne' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400' }}">
                    {{ $routeur->statut === 'en_ligne' ? 'En ligne' : 'Hors ligne' }}
                </span>
                <span class="text-slate-500">
                    <i class="fas fa-arrow-right group-hover:translate-x-1 transition"></i>
                </span>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-12">
            <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-router text-slate-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-white mb-2">Aucun routeur configuré</h3>
            <p class="text-slate-400">Ajoutez un routeur pour commencer à utiliser le Hotspot</p>
            <a href="{{ route('routeurs.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 rounded-lg transition">
                <i class="fas fa-plus"></i>
                Ajouter un routeur
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection
