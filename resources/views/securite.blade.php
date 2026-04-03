@extends('layouts.app')

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <!-- Statistiques sécurité -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-shield-virus"></i> Niveau de sécurité</div>
            <div class="stat-value" style="color: #2ef79b;">{{ $stats['niveau_securite'] ?? 90 }}%</div>
            <div class="stat-change">{{ $stats['niveau_securite'] >= 80 ? 'Excellent' : ($stats['niveau_securite'] >= 50 ? 'Moyen' : 'Faible') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-skull-crosswalk"></i> Tentatives bloquées</div>
            <div class="stat-value">{{ $stats['tentatives_bloc'] ?? 0 }}</div>
            <div class="stat-change">+{{ $stats['tentatives_bloc_today'] ?? 0 }} aujourd'hui</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-firewall"></i> Règles firewall</div>
            <div class="stat-value">{{ $stats['regles_firewall'] ?? 0 }}</div>
            <div class="stat-change">Actives</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-lock"></i> Connexions TLS</div>
            <div class="stat-value">{{ $stats['connexions_tls'] ?? 0 }}</div>
            <div class="stat-change">Chiffrées</div>
        </div>
    </div>

    <!-- Alertes et incidents -->
    <div class="router-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #ffaa33;"></i> Alertes récentes</h3>
                <span class="status-badge">3 non résolues</span>
            </div>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @forelse($stats['alertes_recente'] ?? collect() as $alerte)
                <div style="background: #1f2128; padding: 1rem; border-radius: 1rem; border-left: 4px solid {{ $alerte->severite == 'critique' ? '#ff5e7c' : ($alerte->severite == 'haute' ? '#ffaa33' : '#2ef75b') }};">
                    <div style="display: flex; justify-content: space-between;">
                        <span><i class="fas fa-{{ $alerte->severite == 'critique' ? 'skull-crosswalk' : ($alerte->type == 'intrusion' ? 'exclamation-triangle' : 'check-circle') }}" style="color: {{ $alerte->severite == 'critique' ? '#ff5e7c' : ($alerte->severite == 'haute' ? '#ffaa33' : '#2ef75b') }};"></i> <strong>{{ $alerte->nom_evenement }}</strong></span>
                        <span style="color: #8ba9d0;">{{ $alerte->created_at->diffForHumans() }}</span>
                    </div>
                    <div style="margin-top: 0.5rem;">{{ Str::limit($alerte->description, 100) }}</div>
                </div>
                @empty
                <div style="background: #1f2128; padding: 1rem; border-radius: 1rem; border-left: 4px solid #2ef75b;">
                    <div>Aucune alerte récente.</div>
                </div>
                @endforelse
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-firewall"></i> Règles firewall actives</h3>
                <button class="btn-add" style="padding: 0.3rem 1rem;"><i class="fas fa-plus"></i> Ajouter</button>
            </div>
            <div style="max-height: 300px; overflow-y: auto;">
                <div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;">
                    <div><i class="fas fa-check-circle" style="color: #2ef75b;"></i> <strong>Entrante:</strong> Autoriser LAN → WAN</div>
                </div>
                <div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;">
                    <div><i class="fas fa-check-circle" style="color: #2ef75b;"></i> <strong>Entrante:</strong> Bloquer WAN → LAN (sauf établi)</div>
                </div>
                <div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;">
                    <div><i class="fas fa-check-circle" style="color: #2ef75b;"></i> <strong>Forward:</strong> Limiter P2P</div>
                </div>
                <div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;">
                    <div><i class="fas fa-shield-alt" style="color: #ffaa33;"></i> <strong>NAT:</strong> Masquerade pour LAN</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions actives -->
    <div class="table-section">
        <div class="section-header">
            <h2><i class="fas fa-users"></i> Sessions actives</h2>
            <button class="btn-add"><i class="fas fa-sync-alt"></i> Actualiser</button>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Adresse IP</th>
                        <th>Type</th>
                        <th>Début</th>
                        <th>Durée</th>
                        <th>Trafic</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><i class="fas fa-user-shield"></i> admin</td>
                        <td>10.0.0.15</td>
                        <td>SSH</td>
                        <td>14:32:15</td>
                        <td>2h 15min</td>
                        <td>45 MB</td>
                        <td><button class="action-btn" title="Déconnecter"><i class="fas fa-ban" style="color: #ff5e7c;"></i></button></td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-user"></i> user_wifi</td>
                        <td>10.0.0.112</td>
                        <td>WiFi</td>
                        <td>15:45:22</td>
                        <td>35min</td>
                        <td>128 MB</td>
                        <td><button class="action-btn" title="Déconnecter"><i class="fas fa-ban" style="color: #ff5e7c;"></i></button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection