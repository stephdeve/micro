@extends('layouts.app')

@section('title', 'Modifier employé')

@section('content')
<div class="main-content">
    <div class="section-header" style="margin-bottom:1.5rem;">
        <h2><i class="fas fa-user-edit"></i> Modifier l'employé</h2>
        <a href="{{ route('users.index') }}" class="btn-icon">← Retour</a>
    </div>

    <div class="table-container" style="padding:1.5rem;">
        @if($errors->any())
            <div class="alert error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')

            <div style="display:grid; gap:1rem;">
                <label><span style="color:#98b5cd;">Nom *</span><input type="text" name="name" class="input-field" value="{{ old('name', $user->name) }}" required></label>
                <label><span style="color:#98b5cd;">Email *</span><input type="email" name="email" class="input-field" value="{{ old('email', $user->email) }}" required></label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.8rem;">
                    <label><span style="color:#98b5cd;">Nouveau mot de passe</span><input type="password" name="password" class="input-field"></label>
                    <label><span style="color:#98b5cd;">Confirmer mot de passe</span><input type="password" name="password_confirmation" class="input-field"></label>
                </div>
                <label><span style="color:#98b5cd;">Téléphone</span><input type="text" name="telephone" class="input-field" value="{{ old('telephone', $user->telephone) }}"></label>
                <label><span style="color:#98b5cd;">Fonction</span><input type="text" name="fonction" class="input-field" value="{{ old('fonction', $user->fonction) }}"></label>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.8rem; align-items:center;">
                    <label><span style="color:#98b5cd;">Rôle</span>
                        <select name="role" class="input-field" required>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ (old('role', $user->getRoleNames()->first()) == $role) ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem;"><input type="checkbox" name="est_actif" value="1" {{ old('est_actif', $user->est_actif) ? 'checked' : '' }}> Actif</label>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1rem;">
                    <a href="{{ route('users.index') }}" class="btn-icon">Annuler</a>
                    <button type="submit" class="btn-add">Enregistrer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection