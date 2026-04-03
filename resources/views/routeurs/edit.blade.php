@extends('layouts.app')

@section('title', 'Modifier routeur')

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <div class="card" style="max-width: 800px; margin: 2rem auto; padding: 2rem;">
        <h2>Modifier le routeur</h2>

        <form method="POST" action="{{ route('routeurs.update', $routeur) }}" style="margin-top: 1rem;">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 1rem;">
                <label>Nom</label>
                <input type="text" name="nom" value="{{ old('nom', $routeur->nom) }}" class="input-field" required>
            </div>
            <div style="margin-bottom: 1rem;">
                <label>Modèle</label>
                <input type="text" name="modele" value="{{ old('modele', $routeur->modele) }}" class="input-field">
            </div>
            <div style="margin-bottom: 1rem;">
                <label>Adresse IP</label>
                <input type="text" name="adresse_ip" value="{{ old('adresse_ip', $routeur->adresse_ip) }}" class="input-field" required>
            </div>
            <div style="margin-bottom: 1rem;">
                <label>Statut</label>
                <select name="statut" class="input-field" required>
                    <option value="en_ligne" {{ $routeur->statut == 'en_ligne' ? 'selected' : '' }}>En ligne</option>
                    <option value="hors_ligne" {{ $routeur->statut == 'hors_ligne' ? 'selected' : '' }}>Hors ligne</option>
                    <option value="maintenance" {{ $routeur->statut == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn-primary">Sauvegarder</button>
                <a href="{{ route('routeurs.index') }}" class="btn-icon">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection