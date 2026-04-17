@extends('layouts.app')

@section('title', 'Mon Espace')

@section('content')
<div class="main-content">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1><i class="fas fa-user-circle"></i> Bienvenue, {{ $employe->prenom }} !</h1>
            <p>{{ $employe->poste ?? 'Employé' }} - {{ $employe->departement ?? 'Département non défini' }}</p>
        </div>
        <div class="status-section">
            @if($employe->active)
                @if($employe->last_connected_at && $employe->last_connected_at->gt(now()->subMinutes(5)))
                    <span class="status-badge online">
                        <i class="fas fa-wifi"></i> En ligne
                    </span>
                @else
                    <span class="status-badge offline">
                        <i class="fas fa-wifi"></i> Hors ligne
                    </span>
                @endif
            @else
                <span class="status-badge blocked">
                    <i class="fas fa-ban"></i> Compte bloqué
                </span>
            @endif
        </div>
    </div>

    <!-- Stats principales -->
    <div class="stats-grid">
        <!-- Consommation -->
        <div class="stat-card large">
            <div class="stat-header">
                <i class="fas fa-database"></i>
                <span>Consommation ce mois</span>
            </div>
            <div class="stat-body">
                <div class="usage-display">
                    <span class="usage-value">{{ $employe->dataUsedFormatted() }}</span>
                    <span class="usage-total">/ {{ $employe->quotaFormatted() }}</span>
                </div>
                @if(!$employe->isUnlimited())
                    <div class="usage-bar">
                        @php
                            $percent = $employe->quotaUsedPercent();
                            $colorClass = $percent > 90 ? 'danger' : ($percent > 70 ? 'warning' : 'success');
                        @endphp
                        <div class="usage-fill {{ $colorClass }}" style="width: {{ $percent }}%"></div>
                    </div>
                    <div class="usage-info">
                        <span>{{ round($percent, 1) }}% utilisé</span>
                        <span>{{ $employe->quotaFormatted() }} total</span>
                    </div>
                @else
                    <div class="usage-info">
                        <span>Quota illimité</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Débit en temps réel -->
        <div class="stat-card large">
            <div class="stat-header">
                <i class="fas fa-tachometer-alt"></i>
                <span>Débit actuel</span>
            </div>
            <div class="stat-body speed-body">
                <div class="speed-display">
                    <div class="speed-item">
                        <i class="fas fa-arrow-down"></i>
                        <span id="speed-down" class="speed-value">0</span>
                        <span class="speed-unit">kbps</span>
                    </div>
                    <div class="speed-item">
                        <i class="fas fa-arrow-up"></i>
                        <span id="speed-up" class="speed-value">0</span>
                        <span class="speed-unit">kbps</span>
                    </div>
                </div>
                <p class="speed-note">Mise à jour en temps réel</p>
            </div>
        </div>
    </div>

    <!-- Informations réseau -->
    <div class="info-grid">
        <div class="info-card">
            <h3><i class="fas fa-wifi"></i> Ma Zone WiFi</h3>
            @if($employe->wifiZone)
                <div class="info-content">
                    <div class="info-item">
                        <span class="info-label">Réseau</span>
                        <span class="info-value">{{ $employe->wifiZone->ssid }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Zone</span>
                        <span class="info-value">{{ $employe->wifiZone->nom }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Débit alloué</span>
                        <span class="info-value">{{ $employe->bandwidthFormatted() }}</span>
                    </div>
                </div>
            @else
                <p class="empty-info">Aucune zone WiFi assignée</p>
            @endif
        </div>

        <div class="info-card">
            <h3><i class="fas fa-laptop"></i> Mon Appareil</h3>
            <div class="info-content">
                <div class="info-item">
                    <span class="info-label">Adresse MAC</span>
                    <span class="info-value">{{ $employe->mac_address ?? 'Non enregistrée' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">IP Attribuée</span>
                    <span class="info-value">{{ $employe->ip_address ?? 'Non connecté' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dernière connexion</span>
                    <span class="info-value">
                        @if($employe->last_connected_at)
                            {{ $employe->last_connected_at->diffForHumans() }}
                        @else
                            Jamais
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h3><i class="fas fa-history"></i> Historique de connexions</h3>
            <div class="history-list">
                @forelse($recentConnections as $connection)
                    <div class="history-item">
                        <span class="history-date">{{ $connection['date'] }}</span>
                        <span class="history-duration">{{ $connection['duration'] }}</span>
                        <span class="history-data">{{ $connection['data'] }}</span>
                    </div>
                @empty
                    <p class="empty-info">Aucune connexion récente</p>
                @endforelse
            </div>
            <div class="history-total">
                <span>Durée totale:</span>
                <span>{{ $employe->connectionDurationFormatted() }}</span>
            </div>
        </div>

        <div class="info-card messages-card">
            <h3>
                <i class="fas fa-envelope"></i> Messages
                @if($unreadMessages > 0)
                    <span class="badge badge-danger">{{ $unreadMessages }}</span>
                @endif
            </h3>
            <div class="messages-content">
                @if($unreadMessages > 0)
                    <p class="unread-notice">
                        <i class="fas fa-bell"></i>
                        Vous avez {{ $unreadMessages }} message(s) non lu(s)
                    </p>
                    <a href="#" class="btn-primary btn-sm">
                        <i class="fas fa-envelope-open"></i> Voir les messages
                    </a>
                @else
                    <p class="empty-info">Aucun nouveau message</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quota restant -->
    @if(!$employe->isUnlimited())
        <div class="quota-banner">
            <div class="quota-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="quota-text">
                <span>Quota restant ce mois:</span>
                <span class="quota-remaining">{{ round(($employe->quota_monthly - $employe->data_used_this_month) / 1024, 2) }} Go</span>
            </div>
        </div>
    @endif
</div>

<style>
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .welcome-section h1 {
        margin: 0 0 0.5rem 0;
        color: #fff;
    }
    .welcome-section p {
        margin: 0;
        color: #6b7f96;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
    }
    .status-badge.online {
        background: rgba(46,247,91,0.2);
        color: #2ef75b;
    }
    .status-badge.offline {
        background: rgba(107,127,150,0.2);
        color: #6b7f96;
    }
    .status-badge.blocked {
        background: rgba(255,94,124,0.2);
        color: #ff5e7c;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
        border-radius: 1rem;
        border: 1px solid #2e4b6b;
        padding: 1.5rem;
    }
    .stat-card.large {
        min-height: 200px;
    }

    .stat-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #6b7f96;
        margin-bottom: 1rem;
    }
    .stat-header i {
        color: #00a6ff;
        font-size: 1.2rem;
    }

    .usage-display {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .usage-value {
        font-size: 3rem;
        font-weight: 700;
        color: #fff;
    }
    .usage-total {
        font-size: 1.2rem;
        color: #6b7f96;
    }

    .usage-bar {
        height: 8px;
        background: rgba(0,0,0,0.3);
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.5rem;
    }
    .usage-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.3s;
    }
    .usage-fill.success { background: linear-gradient(90deg, #2ef75b, #20d954); }
    .usage-fill.warning { background: linear-gradient(90deg, #ffaa33, #e6931e); }
    .usage-fill.danger { background: linear-gradient(90deg, #ff5e7c, #e5455c); }

    .usage-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        color: #6b7f96;
    }

    .speed-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .speed-display {
        display: flex;
        justify-content: space-around;
        margin-bottom: 1rem;
    }
    .speed-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    .speed-item i {
        font-size: 2rem;
        color: #00a6ff;
    }
    .speed-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #fff;
    }
    .speed-unit {
        color: #6b7f96;
        font-size: 0.9rem;
    }
    .speed-note {
        text-align: center;
        color: #6b7f96;
        font-size: 0.85rem;
        margin: 0;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .info-card {
        background: linear-gradient(135deg, #1a2a3a 0%, #0f1a24 100%);
        border-radius: 1rem;
        border: 1px solid #2e4b6b;
        padding: 1.5rem;
    }
    .info-card h3 {
        margin: 0 0 1.5rem 0;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .info-card h3 i {
        color: #00a6ff;
    }
    .info-card h3 .badge {
        margin-left: auto;
    }

    .info-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid rgba(46,75,107,0.3);
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-label {
        color: #6b7f96;
        font-size: 0.9rem;
    }
    .info-value {
        color: #fff;
        font-weight: 500;
    }

    .empty-info {
        color: #6b7f96;
        text-align: center;
        padding: 1rem 0;
    }

    .history-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .history-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem;
        background: rgba(0,0,0,0.2);
        border-radius: 0.5rem;
        font-size: 0.9rem;
    }
    .history-date {
        color: #8ba9d0;
    }
    .history-duration {
        color: #fff;
    }
    .history-data {
        color: #00a6ff;
    }
    .history-total {
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(46,75,107,0.3);
        font-weight: 600;
        color: #fff;
    }

    .messages-content {
        text-align: center;
    }
    .unread-notice {
        color: #ffaa33;
        margin-bottom: 1rem;
    }
    .unread-notice i {
        margin-right: 0.5rem;
    }

    .quota-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-top: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(102,126,234,0.2) 0%, rgba(118,75,162,0.2) 100%);
        border-radius: 1rem;
        border: 1px solid #667eea;
    }
    .quota-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea, #764ba2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #fff;
    }
    .quota-text {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
    }
    .quota-text span:first-child {
        color: #8ba9d0;
    }
    .quota-remaining {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 0.3rem 0.6rem;
        border-radius: 0.3rem;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .badge-danger {
        background: rgba(255,94,124,0.2);
        color: #ff5e7c;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-sm {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
</style>

<script>
    // Mise à jour en temps réel du débit
    function updateSpeed() {
        // TODO: Implémenter la récupération réelle depuis le routeur
        // Simulation pour l'instant
        const down = Math.floor(Math.random() * 5000) + 500;
        const up = Math.floor(Math.random() * 2000) + 100;

        document.getElementById('speed-down').textContent = down.toLocaleString();
        document.getElementById('speed-up').textContent = up.toLocaleString();
    }

    // Mettre à jour toutes les 2 secondes
    setInterval(updateSpeed, 2000);
    updateSpeed();
</script>
@endsection
