@extends('layouts.app')

@section('title', $employe->fullName() . ' - Détails')

@section('content')
<div class="main-content">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-user"></i> {{ $employe->fullName() }}</h1>
            <p>{{ $employe->poste ?? 'Employé' }} - {{ $employe->departement ?? 'Sans département' }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('routeurs.employes.index', $routeur) }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <button onclick="toggleEmploye()" class="btn {{ $employe->active ? 'btn-warning' : 'btn-success' }}">
                <i class="fas {{ $employe->active ? 'fa-ban' : 'fa-check' }}"></i>
                {{ $employe->active ? 'Bloquer' : 'Débloquer' }}
            </button>
        </div>
    </div>

    <div class="details-grid">
        <!-- Informations personnelles -->
        <div class="detail-card">
            <h3><i class="fas fa-user-circle"></i> Informations</h3>
            <div class="detail-content">
                <div class="detail-item">
                    <span class="label">Email</span>
                    <span class="value">{{ $employe->email }}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Téléphone</span>
                    <span class="value">{{ $employe->telephone ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Matricule</span>
                    <span class="value">{{ $employe->matricule ?? '-' }}</span>
                </div>
                <div class="detail-item">
                    <span class="label">Statut</span>
                    <span class="value">{!! $employe->statusBadge() !!}</span>
                </div>
            </div>
        </div>

        <!-- Accès réseau -->
        <div class="detail-card">
            <h3><i class="fas fa-wifi"></i> Accès Réseau</h3>
            <div class="detail-content">
                <div class="detail-item">
                    <span class="label">Zone WiFi</span>
                    <span class="value">
                        @if($employe->wifiZone)
                            {{ $employe->wifiZone->nom }} ({{ $employe->wifiZone->ssid }})
                        @else
                            Non assigné
                        @endif
                    </span>
                </div>
                <div class="detail-item">
                    <span class="label">Adresse MAC</span>
                    <span class="value">{{ $employe->mac_address ?? 'Non enregistrée' }}</span>
                </div>
                <div class="detail-item">
                    <span class="label">IP</span>
                    <span class="value">{{ $employe->ip_address ?? 'Non connecté' }}</span>
                </div>
            </div>
        </div>

        <!-- Consommation -->
        <div class="detail-card wide">
            <h3><i class="fas fa-chart-bar"></i> Consommation</h3>
            <div class="stats-row">
                <div class="stat-box">
                    <span class="stat-label">Données utilisées (ce mois)</span>
                    <span class="stat-value">{{ $employe->dataUsedFormatted() }}</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Quota mensuel</span>
                    <span class="stat-value">{{ $employe->quotaFormatted() }}</span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Utilisation</span>
                    <span class="stat-value {{ $stats['quota_percent'] > 90 ? 'danger' : ($stats['quota_percent'] > 70 ? 'warning' : '') }}">
                        {{ round($stats['quota_percent'], 1) }}%
                    </span>
                </div>
                <div class="stat-box">
                    <span class="stat-label">Durée totale</span>
                    <span class="stat-value">{{ $employe->connectionDurationFormatted() }}</span>
                </div>
            </div>
            @if(!$employe->isUnlimited())
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ min(100, $stats['quota_percent']) }}%"></div>
                </div>
            @endif
        </div>

        <!-- Bande passante -->
        <div class="detail-card">
            <h3><i class="fas fa-tachometer-alt"></i> Bande passante</h3>
            <div class="bandwidth-display">
                <div class="band-item">
                    <i class="fas fa-arrow-down"></i>
                    <span>Download</span>
                    <strong>{{ $employe->bandwidth_down > 0 ? $employe->bandwidth_down . ' Mbps' : ($employe->wifiZone ? $employe->wifiZone->bandwidth_down . ' Mbps' : 'Illimité') }}</strong>
                </div>
                <div class="band-item">
                    <i class="fas fa-arrow-up"></i>
                    <span>Upload</span>
                    <strong>{{ $employe->bandwidth_up > 0 ? $employe->bandwidth_up . ' Mbps' : ($employe->wifiZone ? $employe->wifiZone->bandwidth_up . ' Mbps' : 'Illimité') }}</strong>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="detail-card">
            <h3><i class="fas fa-comment"></i> Notes</h3>
            <div class="notes-content">
                {{ $employe->notes ?? 'Aucune note' }}
            </div>
        </div>
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

    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
    }

    .detail-card {
        background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
        border-radius: 1rem;
        border: 1px solid #2e4b6b;
        padding: 1.5rem;
    }
    .detail-card.wide {
        grid-column: 1 / -1;
    }

    .detail-card h3 {
        margin: 0 0 1.5rem 0;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .detail-card h3 i {
        color: #00a6ff;
    }

    .detail-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .detail-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(46,75,107,0.3);
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .label {
        color: #6b7f96;
        font-size: 0.9rem;
    }
    .value {
        color: #fff;
        font-weight: 500;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-box {
        background: rgba(0,0,0,0.2);
        padding: 1rem;
        border-radius: 0.5rem;
        text-align: center;
    }
    .stat-box .stat-label {
        display: block;
        color: #6b7f96;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    .stat-box .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
    }
    .stat-box .stat-value.warning { color: #ffaa33; }
    .stat-box .stat-value.danger { color: #ff5e7c; }

    .progress-bar {
        height: 8px;
        background: rgba(0,0,0,0.3);
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 4px;
    }

    .bandwidth-display {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .band-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(0,0,0,0.2);
        border-radius: 0.5rem;
    }
    .band-item i {
        font-size: 1.5rem;
        color: #00a6ff;
    }
    .band-item span {
        color: #6b7f96;
        flex: 1;
    }
    .band-item strong {
        color: #fff;
        font-size: 1.1rem;
    }

    .notes-content {
        color: #8ba9d0;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .btn-secondary, .btn {
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
        font-size: 0.95rem;
    }
    .btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #fff;
        border: 1px solid #2e4b6b;
    }
    .btn-success {
        background: linear-gradient(135deg, #2ef75b, #20d954);
        color: #fff;
    }
    .btn-warning {
        background: linear-gradient(135deg, #ffaa33, #e6931e);
        color: #fff;
    }

    @media (max-width: 768px) {
        .details-grid {
            grid-template-columns: 1fr;
        }
        .detail-card.wide {
            grid-column: 1;
        }
    }
</style>

<script>
    function toggleEmploye() {
        const action = {{ $employe->active ? 'bloquer' : 'débloquer' }};
        if (confirm(`Êtes-vous sûr de vouloir ${action} cet employé ?`)) {
            fetch('{{ route('routeurs.employes.toggle', [$routeur, $employe]) }}', {
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
    }
</script>
@endsection
