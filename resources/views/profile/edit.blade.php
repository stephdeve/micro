@extends('layouts.app')

@section('title', 'Mon Profil')

@php
    $header_buttons = '';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Messages de succès -->
        @if(session('success'))
            <div style="background: #1e4a3a; border: 1px solid #2ed68a; color: #7ef5c0; padding: 1rem; border-radius: 1rem; margin-bottom: 2rem;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- En-tête du profil -->
        <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <!-- Avatar -->
            <div style="position: relative;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(145deg, #2a5f8a, #1e4b73); border: 4px solid #4fc3ff; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                    @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        <span style="font-size: 3rem; font-weight: 600; color: white;">{{ substr($user->name ?? 'U', 0, 1) }}</span>
                    @endif
                </div>
                
                <!-- Bouton pour changer l'avatar -->
                <form id="avatar-form" action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" style="position: absolute; bottom: 0; right: 0;">
                    @csrf
                    <label for="avatar-input" style="width: 36px; height: 36px; border-radius: 50%; background: #1f3145; border: 2px solid #4fc3ff; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;" title="Changer l'avatar">
                        <i class="fas fa-camera" style="color: #aac6ff;"></i>
                    </label>
                    <input type="file" id="avatar-input" name="avatar" accept="image/*" style="display: none;" onchange="document.getElementById('avatar-form').submit();">
                </form>

                <!-- Bouton pour supprimer l'avatar (si existe) -->
                @if($user->avatar)
                <form action="{{ route('profile.avatar.delete') }}" method="POST" style="position: absolute; top: 0; right: 0;" onsubmit="return confirm('Supprimer l\'avatar ?');">
                    @csrf
                    <button type="submit" style="width: 36px; height: 36px; border-radius: 50%; background: #8f2e2e; border: 2px solid #ff6b6b; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.2s;" title="Supprimer l'avatar">
                        <i class="fas fa-trash" style="color: #ff9f9f;"></i>
                    </button>
                </form>
                @endif
            </div>

            <!-- Infos rapides -->
            <div>
                <h2 style="font-size: 2rem; color: white;">{{ $user->name }}</h2>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem; flex-wrap: wrap;">
                    <span style="background: #1f3a4b; padding: 0.3rem 1rem; border-radius: 30px; border: 1px solid #2f93b0; color: #a2ecff;">
                        <i class="fas fa-shield-alt"></i> {{ $user->roles->first()->name ?? 'superuser' }}
                    </span>
                    <span style="background: #1e4a3a; padding: 0.3rem 1rem; border-radius: 30px; border: 1px solid #2ed68a; color: #7ef5c0;">
                        <i class="fas fa-check-circle"></i> Actif
                    </span>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="card" style="max-width: 600px;">
            <div class="card-header">
                <h3><i class="fas fa-user-edit"></i> Modifier mes informations</h3>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <!-- Nom -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Nom complet</label>
                    <input type="text" name="name" class="input-field" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div style="color: #ff9494; font-size: 0.85rem; margin-top: 0.3rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Email</label>
                    <input type="email" name="email" class="input-field" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div style="color: #ff9494; font-size: 0.85rem; margin-top: 0.3rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Téléphone -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Téléphone</label>
                    <input type="text" name="telephone" class="input-field" value="{{ old('telephone', $user->telephone) }}">
                    @error('telephone')
                        <div style="color: #ff9494; font-size: 0.85rem; margin-top: 0.3rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Fonction -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Fonction</label>
                    <input type="text" name="fonction" class="input-field" value="{{ old('fonction', $user->fonction) }}">
                    @error('fonction')
                        <div style="color: #ff9494; font-size: 0.85rem; margin-top: 0.3rem;">{{ $message }}</div>
                    @enderror
                </div>

                <hr style="border-color: #263f55; margin: 2rem 0;">

                <h4 style="margin-bottom: 1.5rem;"><i class="fas fa-lock"></i> Changer le mot de passe</h4>

                <!-- Mot de passe actuel -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="input-field">
                    @error('current_password')
                        <div style="color: #ff9494; font-size: 0.85rem; margin-top: 0.3rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nouveau mot de passe -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Nouveau mot de passe</label>
                    <input type="password" name="new_password" class="input-field">
                    @error('new_password')
                        <div style="color: #ff9494; font-size: 0.85rem; margin-top: 0.3rem;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirmation -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="new_password_confirmation" class="input-field">
                </div>

                <!-- Boutons -->
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                    <a href="{{ route('dashboard') }}" class="btn-icon" style="width: auto; padding: 0 1.5rem; border-radius: 2rem;">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection