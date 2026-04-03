@extends('layouts.app')

@section('title', 'Ajouter interface')

@php
    $header_buttons = '<a href="'.route('interfaces.index').'" class="btn-secondary" style="width: auto; padding: 0.65rem 1.2rem; border-radius: 2rem;"><i class="fas fa-arrow-left" style="margin-right: 0.4rem;"></i>Retour</a>';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg" style="display:none;"></div>
    @include('layouts.guest')

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header"><h2><i class="fas fa-plus"></i> Ajouter une interface</h2></div>
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('interfaces.store') }}">
                @csrf

                <div class="input-group"><label>Routeur</label>
                    <select name="routeur_id" class="input-field" required>
                        <option value="">-- Choisir --</option>
                        @foreach($routeurs as $routeur)
                            <option value="{{ $routeur->id }}">{{ $routeur->nom }} ({{ $routeur->adresse_ip }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="input-group"><label>Nom</label><input type="text" name="nom" class="input-field" required></div>
                <div class="input-group"><label>Type</label>
                    <select name="type" class="input-field" required>
                        <option value="ethernet">Ethernet</option>
                        <option value="wifi">WiFi</option>
                        <option value="bridge">Bridge</option>
                        <option value="vlan">VLAN</option>
                    </select>
                </div>
                <div class="input-group"><label>Adresse MAC</label><input name="adresse_mac" class="input-field"></div>
                <div class="input-group"><label>Adresse IP</label><input name="adresse_ip" class="input-field"></div>
                <div class="input-group"><label>Statut</label>
                    <select name="statut" class="input-field" required>
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="erreur">Erreur</option>
                    </select>
                </div>

                <div style="display:flex; gap: 0.8rem; margin-top:1rem;">
                    <button class="btn-add" type="submit"><i class="fas fa-save"></i> Enregistrer</button>
                    <a class="btn-icon" href="{{ route('interfaces.index') }}">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection