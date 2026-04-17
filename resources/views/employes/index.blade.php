@extends('layouts.app')

@section('title', 'Employés - ' . $routeur->nom)

@section('content')
<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-users"></i> Employés - {{ $routeur->nom }}</h1>
            <p>Gestion des utilisateurs réseau et leurs accès WiFi</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('routeurs.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button onclick="openModal('add')" class="btn-primary">
                <i class="fas fa-plus"></i> Nouvel Employé
            </button>
        </div>
    </div>

    <!-- Alertes -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['total'] }}</span>
                <span class="stat-label">Total Employés</span>
            </div>
        </div>
        <div class="stat-card online">
            <div class="stat-icon"><i class="fas fa-wifi"></i></div>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['online'] }}</span>
                <span class="stat-label">En Ligne</span>
            </div>
        </div>
        <div class="stat-card blocked">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-info">
                <span class="stat-value">{{ $stats['blocked'] }}</span>
                <span class="stat-label">Bloqués</span>
            </div>
        </div>
    </div>

    <!-- Liste des employés -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Liste des Employés</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employé</th>
                            <th>Zone WiFi</th>
                            <th>Débit</th>
                            <th>Quota</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employes as $employe)
                            <tr class="{{ !$employe->active ? 'row-inactive' : '' }}">
                                <td>
                                    <div class="employee-info">
                                        <div class="employee-avatar">{{ substr($employe->prenom, 0, 1) }}{{ substr($employe->nom, 0, 1) }}</div>
                                        <div class="employee-details">
                                            <span class="employee-name">{{ $employe->fullName() }}</span>
                                            <span class="employee-email">{{ $employe->email }}</span>
                                            @if($employe->matricule)
                                                <span class="employee-matricule">Mat: {{ $employe->matricule }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($employe->wifiZone)
                                        <span class="zone-badge">{{ $employe->wifiZone->nom }}</span>
                                    @else
                                        <span class="zone-badge none">Non assigné</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="bandwidth-info">{{ $employe->bandwidthFormatted() }}</span>
                                </td>
                                <td>
                                    <div class="quota-info">
                                        <span>{{ $employe->dataUsedFormatted() }} / {{ $employe->quotaFormatted() }}</span>
                                        @if(!$employe->isUnlimited())
                                            <div class="quota-bar">
                                                <div class="quota-fill" style="width: {{ $employe->quotaUsedPercent() }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($employe->last_connected_at && $employe->last_connected_at->gt(now()->subMinutes(5)))
                                        <span class="badge badge-success"><i class="fas fa-circle"></i> En ligne</span>
                                    @else
                                        <span class="badge badge-secondary">Hors ligne</span>
                                    @endif
                                    @if(!$employe->active)
                                        <span class="badge badge-danger">Bloqué</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button onclick="viewEmploye({{ $employe->id }})" class="btn-icon" title="Voir détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button onclick="editEmploye({{ $employe->id }})" class="btn-icon" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="toggleEmploye({{ $employe->id }}, '{{ $employe->fullName() }}', {{ $employe->active ? 'true' : 'false' }})" 
                                                class="btn-icon {{ $employe->active ? 'btn-warning' : 'btn-success' }}" 
                                                title="{{ $employe->active ? 'Bloquer' : 'Débloquer' }}">
                                            <i class="fas {{ $employe->active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                        <button onclick="deleteEmploye({{ $employe->id }}, '{{ $employe->fullName() }}')" class="btn-icon btn-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="empty-row">
                                    <i class="fas fa-users"></i>
                                    <p>Aucun employé enregistré</p>
                                    <button onclick="openModal('add')" class="btn-primary">
                                        <i class="fas fa-plus"></i> Ajouter un employé
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $employes->links() }}
        </div>
    </div>
</div>

<!-- Modal Employé -->
<div id="employeModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-user-plus"></i> Nouvel Employé</h3>
            <button onclick="closeModal()" class="btn-close">&times;</button>
        </div>
        <form id="employeForm" method="POST" action="{{ route('routeurs.employes.store', $routeur) }}">
            @csrf
            <input type="hidden" id="method" value="POST">
            <input type="hidden" id="employeId">

            <div class="form-section">
                <h4><i class="fas fa-user"></i> Informations personnelles</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="prenom">Prénom *</label>
                        <input type="text" id="prenom" name="prenom" class="input-field" required>
                    </div>
                    <div class="form-group">
                        <label for="nom">Nom *</label>
                        <input type="text" id="nom" name="nom" class="input-field" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="input-field" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" class="input-field">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4><i class="fas fa-briefcase"></i> Informations professionnelles</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="matricule">Matricule</label>
                        <input type="text" id="matricule" name="matricule" class="input-field">
                    </div>
                    <div class="form-group">
                        <label for="departement">Département</label>
                        <input type="text" id="departement" name="departement" class="input-field" placeholder="ex: Direction, IT, RH...">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="poste">Poste</label>
                        <input type="text" id="poste" name="poste" class="input-field" placeholder="ex: Manager, Développeur...">
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4><i class="fas fa-wifi"></i> Accès Réseau</h4>
                <div class="form-row">
                    <div class="form-group">
                        <label for="wifi_zone_id">Zone WiFi</label>
                        <select id="wifi_zone_id" name="wifi_zone_id" class="input-field">
                            <option value="">-- Sélectionnez une zone --</option>
                            @foreach($wifiZones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->nom }} ({{ $zone->ssid }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mac_address">Adresse MAC</label>
                        <input type="text" id="mac_address" name="mac_address" class="input-field" placeholder="AA:BB:CC:DD:EE:FF">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="bandwidth_down">Débit Download (Mbps)</label>
                        <input type="number" id="bandwidth_down" name="bandwidth_down" class="input-field" placeholder="0 = illimité" min="0">
                        <small>0 = hérite de la zone WiFi</small>
                    </div>
                    <div class="form-group">
                        <label for="bandwidth_up">Débit Upload (Mbps)</label>
                        <input type="number" id="bandwidth_up" name="bandwidth_up" class="input-field" placeholder="0 = illimité" min="0">
                        <small>0 = hérite de la zone WiFi</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="quota_monthly">Quota mensuel (Mo)</label>
                        <input type="number" id="quota_monthly" name="quota_monthly" class="input-field" placeholder="0 = illimité" min="0">
                        <small>0 = illimité, sinon quota en Mo</small>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4><i class="fas fa-lock"></i> Compte Utilisateur</h4>
                <div class="form-check">
                    <input type="checkbox" id="create_user_account" name="create_user_account" value="1">
                    <label for="create_user_account">
                        <i class="fas fa-user-circle"></i> Créer un compte utilisateur
                        <small>L'employé pourra se connecter avec son email et recevra un mot de passe temporaire</small>
                    </label>
                </div>
            </div>

            <div class="form-section">
                <h4><i class="fas fa-comment"></i> Notes</h4>
                <div class="form-group">
                    <textarea id="notes" name="notes" class="input-field" rows="3" placeholder="Remarques sur l'employé..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" onclick="closeModal()" class="btn-secondary">Annuler</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .header-actions {
        display: flex;
        gap: 0.5rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .stat-card {
        background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border: 1px solid #2e4b6b;
    }
    .stat-card.online {
        border-color: #2ef75b;
    }
    .stat-card.blocked {
        border-color: #ff5e7c;
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(0,166,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #00a6ff;
    }
    .stat-card.online .stat-icon {
        background: rgba(46,247,91,0.1);
        color: #2ef75b;
    }
    .stat-card.blocked .stat-icon {
        background: rgba(255,94,124,0.1);
        color: #ff5e7c;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #fff;
        display: block;
    }
    .stat-label {
        color: #6b7f96;
        font-size: 0.9rem;
    }

    .card {
        background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
        border-radius: 1rem;
        border: 1px solid #2e4b6b;
        overflow: hidden;
    }
    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid #2e4b6b;
    }
    .card-header h3 {
        margin: 0;
        color: #fff;
    }
    .card-body {
        padding: 1.5rem;
    }

    .employee-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #fff;
        font-size: 0.9rem;
    }
    .employee-details {
        display: flex;
        flex-direction: column;
        gap: 0.2rem;
    }
    .employee-name {
        font-weight: 600;
        color: #fff;
    }
    .employee-email {
        font-size: 0.85rem;
        color: #6b7f96;
    }
    .employee-matricule {
        font-size: 0.8rem;
        color: #8ba9d0;
    }

    .zone-badge {
        background: rgba(102,126,234,0.2);
        color: #667eea;
        padding: 0.3rem 0.8rem;
        border-radius: 0.5rem;
        font-size: 0.85rem;
    }
    .zone-badge.none {
        background: rgba(255,170,51,0.2);
        color: #ffaa33;
    }

    .bandwidth-info {
        font-size: 0.85rem;
        color: #8ba9d0;
    }

    .quota-info {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    .quota-info span {
        font-size: 0.85rem;
        color: #8ba9d0;
    }
    .quota-bar {
        height: 4px;
        background: rgba(0,0,0,0.3);
        border-radius: 2px;
        overflow: hidden;
    }
    .quota-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 2px;
        transition: width 0.3s;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.3rem 0.6rem;
        border-radius: 0.3rem;
        font-size: 0.8rem;
    }
    .badge-success {
        background: rgba(46,247,91,0.2);
        color: #2ef75b;
    }
    .badge-secondary {
        background: rgba(107,127,150,0.2);
        color: #6b7f96;
    }
    .badge-danger {
        background: rgba(255,94,124,0.2);
        color: #ff5e7c;
        margin-left: 0.3rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.3rem;
    }

    .row-inactive {
        opacity: 0.6;
        background: rgba(255,94,124,0.05);
    }

    .empty-row {
        text-align: center;
        padding: 3rem !important;
    }
    .empty-row i {
        font-size: 3rem;
        color: #2e4b6b;
        margin-bottom: 1rem;
        display: block;
    }

    /* Modal */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 2rem;
    }
    .modal-content {
        background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
        border-radius: 1rem;
        border: 1px solid #2e4b6b;
        max-width: 700px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #2e4b6b;
    }
    .modal-header h3 {
        margin: 0;
        color: #fff;
    }

    .form-section {
        padding: 1.5rem;
        border-bottom: 1px solid #2e4b6b;
    }
    .form-section h4 {
        color: #00a6ff;
        margin-bottom: 1rem;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 600px) {
        .form-row { grid-template-columns: 1fr; }
    }

    .form-check {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem;
        background: rgba(0,0,0,0.2);
        border-radius: 0.5rem;
    }
    .form-check input {
        margin-top: 0.3rem;
    }
    .form-check label {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        cursor: pointer;
    }
    .form-check label small {
        color: #6b7f96;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        padding: 1.5rem;
    }

    .btn-secondary, .btn-primary, .btn-icon, .btn-close {
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }
    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid #2e4b6b;
    }
    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
    }
    .btn-icon {
        padding: 0.5rem;
        background: rgba(255,255,255,0.1);
        color: #fff;
    }
    .btn-icon.btn-success {
        background: rgba(46,247,91,0.2);
        color: #2ef75b;
    }
    .btn-icon.btn-warning {
        background: rgba(255,170,51,0.2);
        color: #ffaa33;
    }
    .btn-icon.btn-danger {
        background: rgba(255,94,124,0.2);
        color: #ff5e7c;
    }
    .btn-close {
        background: none;
        border: none;
        color: #6b7f96;
        font-size: 1.5rem;
        padding: 0.3rem;
    }

    .input-field {
        width: 100%;
        padding: 0.75rem;
        background: rgba(0,0,0,0.2);
        border: 1px solid #2e4b6b;
        border-radius: 0.5rem;
        color: #fff;
        font-size: 0.95rem;
    }
    .input-field:focus {
        outline: none;
        border-color: #00a6ff;
    }

    small {
        display: block;
        color: #6b7f96;
        margin-top: 0.3rem;
        font-size: 0.8rem;
    }
</style>

<script>
    function openModal(mode, employeId = null) {
        const modal = document.getElementById('employeModal');
        const form = document.getElementById('employeForm');
        const title = document.getElementById('modalTitle');

        if (mode === 'edit' && employeId) {
            title.innerHTML = '<i class="fas fa-user-edit"></i> Modifier Employé';
            form.action = `/routeurs/{{ $routeur->id }}/employes/${employeId}`;
            document.getElementById('method').value = 'PUT';
            
            // Charger les données
            fetch(`/routeurs/{{ $routeur->id }}/employes/${employeId}/edit`)
                .then(r => r.json())
                .then(data => {
                    const emp = data.employe;
                    document.getElementById('employeId').value = emp.id;
                    document.getElementById('prenom').value = emp.prenom;
                    document.getElementById('nom').value = emp.nom;
                    document.getElementById('email').value = emp.email;
                    document.getElementById('telephone').value = emp.telephone || '';
                    document.getElementById('matricule').value = emp.matricule || '';
                    document.getElementById('departement').value = emp.departement || '';
                    document.getElementById('poste').value = emp.poste || '';
                    document.getElementById('wifi_zone_id').value = emp.wifi_zone_id || '';
                    document.getElementById('mac_address').value = emp.mac_address || '';
                    document.getElementById('bandwidth_down').value = emp.bandwidth_down;
                    document.getElementById('bandwidth_up').value = emp.bandwidth_up;
                    document.getElementById('quota_monthly').value = emp.quota_monthly;
                    document.getElementById('notes').value = emp.notes || '';
                });
        } else {
            title.innerHTML = '<i class="fas fa-user-plus"></i> Nouvel Employé';
            form.action = '{{ route('routeurs.employes.store', $routeur) }}';
            form.reset();
            document.getElementById('method').value = 'POST';
            document.getElementById('employeId').value = '';
        }

        modal.style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('employeModal').style.display = 'none';
    }

    function viewEmploye(employeId) {
        window.location.href = `/routeurs/{{ $routeur->id }}/employes/${employeId}`;
    }

    function editEmploye(employeId) {
        openModal('edit', employeId);
    }

    function toggleEmploye(employeId, name, isActive) {
        const action = isActive ? 'bloquer' : 'débloquer';
        if (!confirm(`Êtes-vous sûr de vouloir ${action} ${name} ?`)) {
            return;
        }

        fetch(`/routeurs/{{ $routeur->id }}/employes/${employeId}/toggle`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    function deleteEmploye(employeId, name) {
        if (!confirm(`Êtes-vous sûr de vouloir supprimer ${name} ? Cette action est irréversible.`)) {
            return;
        }

        fetch(`/routeurs/{{ $routeur->id }}/employes/${employeId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }

    // Fermer le modal en cliquant en dehors
    document.getElementById('employeModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endsection
