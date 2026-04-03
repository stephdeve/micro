@extends('layouts.app')

@section('title', 'Gestion des employés')

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-user-shield"></i><i class="fas fa-users"></i><i class="fas fa-user-plus"></i>
    </div>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="section-header" style="margin-bottom: 1.5rem;">
        <h2><i class="fas fa-users"></i> Gestion des employés</h2>
        <button class="btn-add" onclick="document.getElementById('create-user-modal').style.display = 'flex';"> <i class="fas fa-user-plus"></i> Nouvel employé</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Fonction</th>
                    <th>Téléphone</th>
                    <th>Actif</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->getRoleNames()->first() ?? '—' }}</td>
                        <td>{{ $user->fonction ?? '—' }}</td>
                        <td>{{ $user->telephone ?? '—' }}</td>
                        <td>{{ $user->est_actif ? 'Oui' : 'Non' }}</td>
                        <td>
                            <a href="{{ route('users.edit', $user) }}" class="action-btn edit" style="padding: 0.45rem 0.6rem; font-size: 0.8rem;"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete" style="padding: 0.45rem 0.6rem; font-size: 0.8rem;" onclick="return confirm('Confirmer la suppression ?');"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">Aucun utilisateur trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="pagination-wrapper custom-pagination" style="margin-top: 1.5rem; display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
                <a href="{{ $users->url(1) }}" class="page-item {{ $users->onFirstPage() ? 'disabled' : '' }}">« Début</a>
                <a href="{{ $users->previousPageUrl() ?: '#' }}" class="page-item {{ $users->onFirstPage() ? 'disabled' : '' }}">‹</a>

                @foreach(range(max(1, $users->currentPage() - 2), min($users->lastPage(), $users->currentPage() + 2)) as $page)
                    <a href="{{ $users->url($page) }}" class="page-item {{ $users->currentPage() == $page ? 'active' : '' }}">{{ $page }}</a>
                @endforeach

                <a href="{{ $users->nextPageUrl() ?: '#' }}" class="page-item {{ $users->currentPage() == $users->lastPage() ? 'disabled' : '' }}">›</a>
                <a href="{{ $users->url($users->lastPage()) }}" class="page-item {{ $users->currentPage() == $users->lastPage() ? 'disabled' : '' }}">Fin »</a>
            </div>

            <div style="text-align:center; margin-top:0.7rem; color:#8ba9d0; font-size:0.9rem;">
                Page {{ $users->currentPage() }} / {{ $users->lastPage() }} — {{ $users->total() }} employés
            </div>
        @endif

    </div>
</div>

<div id="create-user-modal" class="modal" style="display:none; align-items:center; justify-content:center;">
    <div class="modal-content" style="max-width:600px; padding: 1.7rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
            <h3 style="color:white;"><i class="fas fa-user-plus"></i> Créer un employé</h3>
            <button type="button" onclick="document.getElementById('create-user-modal').style.display='none';" class="btn-icon">&times;</button>
        </div>

        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div style="display:grid; gap:1rem;">
                <label><span style="color:#98b5cd;">Nom *</span><input type="text" name="name" class="input-field" value="{{ old('name') }}" required></label>
                <label><span style="color:#98b5cd;">Email *</span><input type="email" name="email" class="input-field" value="{{ old('email') }}" required></label>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.8rem;">
                    <label><span style="color:#98b5cd;">Mot de passe *</span><input type="password" name="password" class="input-field" required></label>
                    <label><span style="color:#98b5cd;">Confirmer mot de passe *</span><input type="password" name="password_confirmation" class="input-field" required></label>
                </div>
                <label><span style="color:#98b5cd;">Téléphone</span><input type="text" name="telephone" class="input-field" value="{{ old('telephone') }}"></label>
                <label><span style="color:#98b5cd;">Fonction</span><input type="text" name="fonction" class="input-field" value="{{ old('fonction') }}"></label>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:0.8rem; align-items:center;">
                    <label><span style="color:#98b5cd;">Rôle</span>
                        <select name="role" class="input-field" required>
                            <option value="">-- Choisir --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label style="display:flex; align-items:center; gap:0.5rem;"><input type="checkbox" name="est_actif" value="1" {{ old('est_actif') ? 'checked' : '' }}> Actif</label>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1rem;">
                    <button type="button" class="btn-icon" onclick="document.getElementById('create-user-modal').style.display='none';">Annuler</button>
                    <button type="submit" class="btn-add">Créer</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection