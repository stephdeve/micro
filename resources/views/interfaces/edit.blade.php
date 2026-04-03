@extends('layouts.app')

@section('title', 'Modifier interface')

@php
    $header_buttons = '<a href="'.route('interfaces.index').'" class="btn-secondary" style="width: auto; padding: 0.65rem 1.2rem; border-radius: 2rem;"><i class="fas fa-arrow-left" style="margin-right: 0.4rem;"></i>Retour</a>';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg" style="display:none;"></div>
    @include('layouts.guest')

    <div class="card" style="max-width: 800px; margin: 0 auto;">
        <div class="card-header"><h2><i class="fas fa-edit"></i> Modifier {{ $interface->nom }}</h2></div>
        <div style="padding: 1.5rem;">
            <form method="POST" action="{{ route('interfaces.update', $interface) }}">
                @csrf
                @method('PUT')

                <div class="input-group"><label>Routeur</label>
                    <select name="routeur_id" class="input-field" required>
                        @foreach($routeurs as $routeur)
                            <option value="{{ $routeur->id }}" {{ $interface->routeur_id == $routeur->id ? 'selected' : '' }}>{{ $routeur->nom }} ({{ $routeur->adresse_ip }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="input-group"><label>Nom</label><input type="text" name="nom" class="input-field" value="{{ $interface->nom }}" required></div>
                <div class="input-group"><label>Type</label>
                    <select name="type" class="input-field" required>
                        <option value="ethernet" {{ $interface->type == 'ethernet' ? 'selected' : '' }}>Ethernet</option>
                        <option value="wifi" {{ $interface->type == 'wifi' ? 'selected' : '' }}>WiFi</option>
                        <option value="bridge" {{ $interface->type == 'bridge' ? 'selected' : '' }}>Bridge</option>
                        <option value="vlan" {{ $interface->type == 'vlan' ? 'selected' : '' }}>VLAN</option>
                    </select>
                </div>
                <div class="input-group"><label>Adresse MAC</label><input name="adresse_mac" class="input-field" value="{{ $interface->adresse_mac }}"></div>
                <div class="input-group"><label>Adresse IP</label><input name="adresse_ip" class="input-field" value="{{ $interface->adresse_ip }}"></div>
                <div class="input-group"><label>Statut</label>
                    <select name="statut" class="input-field" required>
                        <option value="actif" {{ $interface->statut == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ $interface->statut == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="erreur" {{ $interface->statut == 'erreur' ? 'selected' : '' }}>Erreur</option>
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