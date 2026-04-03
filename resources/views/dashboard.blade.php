@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="main-content">
    @php
        $header_buttons = '<button class="btn-primary"><i class="fas fa-sync-alt"></i> Synchroniser</button>';
    @endphp

    @include('layouts.guest')

    <!-- Statistiques dynamiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-network-wired"></i> Routeurs actifs</div>
            <div class="stat-value">{{ $routeursActifs ?? 4 }}</div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> +1 depuis hier</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-wifi"></i> Clients WiFi</div>
            <div class="stat-value">{{ $clientsWiFi ?? 58 }}</div>
            <div class="stat-change"><i class="fas fa-arrow-down"></i> -3 actuellement</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-envelope"></i> Messages sécurisés</div>
            <div class="stat-value">{{ $messagesNonLus ?? 142 }}</div>
            <div class="stat-change"><i class="fas fa-clock"></i> {{ $messagesNonLus ?? 12 }} non lus</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-shield-alt"></i> État du réseau</div>
            <div class="stat-value" style="color: #2ef79b;">{{ $etatReseau ?? 'Sécurisé' }}</div>
            <div class="stat-change"><i class="fas fa-check-circle"></i> aucun incident</div>
        </div>
    </div>

    <!-- Section Routeur MikroTik -->
    <div class="router-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-microchip"></i> {{ $routeurPrincipal->nom ?? 'MikroTik RB951G-2HnD' }}</h3>
                <span class="status-badge">
                    <i class="fas fa-circle" style="color: {{ ($routeurPrincipal->statut ?? 'en_ligne') == 'en_ligne' ? '#2ef75b' : '#ff5e7c' }}; font-size: 0.6rem;"></i> 
                    {{ $routeurPrincipal->statut ?? 'en ligne' }}
                </span>
            </div>
            <div class="interface-list">
                @forelse($interfacesPrincipales ?? [] as $interface)
                <div class="interface-item">
                    <div class="interface-info">
                        <span class="led-{{ $interface->statut == 'actif' ? 'green' : 'yellow' }}"></span>
                        <span>{{ $interface->nom }} ({{ Str::title($interface->type ?? 'inconnu') }})</span>
                    </div>
                    <span class="traffic">⬇️ {{ number_format($interface->debit_entrant ?? 0, 1) }} Mb/s ⬆️ {{ number_format($interface->debit_sortant ?? 0, 1) }} Mb/s</span>
                </div>
                @empty
                <div class="interface-item">
                    <div class="interface-info"><span class="led-yellow"></span> <span>Aucune interface disponible</span></div>
                    <span class="traffic">--</span>
                </div>
                @endforelse
            </div>
            <div class="bandwidth-graph">
                @foreach($graphData ?? [65,90,45,80,95,70] as $index => $height)
                <div style="flex:1; text-align: center;">
                    <div class="bar" style="height: {{ $height }}%;"></div>
                    <div class="bar-label">{{ $index * 4 }}h</div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie"></i> Utilisation bande passante</h3>
                <i class="fas fa-ellipsis-h"></i>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; color: #aaccff;">
                    <span>Total: {{ $bandePassanteTotale ?? 210 }} Mbps</span> 
                    <span>{{ $pourcentageUtilisation ?? 47 }}% utilisé</span>
                </div>
                <div style="height: 12px; background: #1a2c3c; border-radius: 20px; margin: 0.5rem 0 2rem;">
                    <div style="width: {{ $pourcentageUtilisation ?? 47 }}%; height: 12px; background: linear-gradient(90deg, #00ccff, #904eff); border-radius: 20px;"></div>
                </div>
            </div>
            <h4 style="margin-bottom: 1rem;">Top consommateurs</h4>
            @forelse($topConsommateurs ?? [] as $consommateur)
            <div style="margin: 0.7rem 0;">
                <i class="fas fa-{{ $consommateur->icone ?? 'laptop' }}"></i> 
                {{ $consommateur->adresse_ip ?? '192.168.1.45' }} · {{ $consommateur->debit ?? 34 }} Mbps
            </div>
            @empty
            <div><i class="fas fa-tv"></i> 192.168.1.45 · 34 Mbps</div>
            <div style="margin: 0.7rem 0;"><i class="fas fa-laptop"></i> 192.168.1.112 · 22 Mbps</div>
            <div><i class="fas fa-phone-alt"></i> 192.168.1.78 · 18 Mbps</div>
            @endforelse
        </div>
    </div>

    <!-- Tableau des enregistrements -->
    <div class="table-section">
        <div class="section-header">
            <h2><i class="fas fa-database" style="color: #4fc3ff;"></i> Gestion des enregistrements</h2>
            <a href="{{ route('routeurs.index', ['create' => 1]) }}" class="btn-add"><i class="fas fa-plus"></i> Nouvel enregistrement</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Modèle</th>
                        <th>Adresse IP</th>
                        <th>Statut</th>
                        <th>Dernière connexion</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enregistrements ?? [] as $enregistrement)
                    <tr>
                        <td>{{ $enregistrement->nom }}</td>
                        <td>{{ $enregistrement->modele ?? 'N/A' }}</td>
                        <td>{{ $enregistrement->adresse_ip }}</td>
                        <td>
                            <div class="status-cell">
                                <span class="status-{{ $enregistrement->statut == 'en_ligne' ? 'active' : ($enregistrement->statut == 'maintenance' ? 'inactive' : 'inactive') }}">
                                    {{ $enregistrement->statut == 'en_ligne' ? 'En ligne' : ($enregistrement->statut == 'maintenance' ? 'Maintenance' : 'Hors ligne') }}
                                </span>
                            </div>
                        </td>
                        <td>{{ $enregistrement->derniere_connexion ? $enregistrement->derniere_connexion->format('Y-m-d H:i') : 'N/A' }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" onclick="window.location='{{ route('routeurs.show', $enregistrement) }}'" title="Voir"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" onclick="window.location='{{ route('routeurs.edit', $enregistrement) }}'" title="Modifier"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('routeurs.destroy', $enregistrement) }}" method="POST" style="display: inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-btn delete" type="submit" title="Supprimer"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td>Routeur Principal</td>
                        <td>MikroTik RB951G</td>
                        <td>192.168.1.1</td>
                        <td><div class="status-cell"><span class="status-active">Actif</span></div></td>
                        <td>2024-01-15 14:32</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" title="Voir"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Switch Core</td>
                        <td>MikroTik CRS326</td>
                        <td>192.168.1.2</td>
                        <td><div class="status-cell"><span class="status-active">Actif</span></div></td>
                        <td>2024-01-15 13:15</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" title="Voir"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Messagerie sécurisée -->
    <div class="messaging-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-envelope-open-text"></i> Derniers messages</h3>
                <span class="status-badge">chiffré TLS</span>
            </div>
            @forelse($derniersMessages ?? [] as $message)
            <div class="message-item">
                <div class="message-header">
                    <span class="message-sender">
                        <i class="fas fa-user-{{ $message->sender_id == Auth::id() ? 'edit' : 'secret' }}"></i> 
                        {{ $message->sender->name ?? 'admin@local' }}
                    </span>
                    <span class="lock-badge"><i class="fas fa-lock"></i> secure</span>
                </div>
                <div class="message-preview">🔒 {{ Str::limit($message->content, 80) ?? 'Configuration du VLAN 10 validée' }}</div>
            </div>
            @empty
            <div class="message-item">
                <div class="message-header">
                    <span class="message-sender"><i class="fas fa-user-secret"></i> admin@local</span>
                    <span class="lock-badge"><i class="fas fa-lock"></i> secure</span>
                </div>
                <div class="message-preview">🔒 Configuration du VLAN 10 validée, aucun conflit d'adressage.</div>
            </div>
            <div class="message-item">
                <div class="message-header">
                    <span class="message-sender"><i class="fas fa-user"></i> nms@mikrotik</span>
                    <span class="lock-badge"><i class="fas fa-lock"></i> secure</span>
                </div>
                <div class="message-preview">🔒 Demande d'accès pour mise à jour firmware RB4011</div>
            </div>
            @endforelse
            <div class="new-message">
                <i class="fas fa-plus-circle"></i> Nouveau message sécurisé
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-shield-virus"></i> Sécurité & Alertes</h3>
                <i class="fas fa-check-circle" style="color: #42f58d;"></i>
            </div>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div><i class="fas fa-check-circle" style="color:#25d366;"></i> Connexions SSL/TLS actives ({{ $connexionsTLS ?? 4 }})</div>
                <div><i class="fas fa-shield-alt" style="color:#ffb74d;"></i> Firewall: {{ $reglesFirewall ?? 3 }} règles actives</div>
                <div><i class="fas fa-skull-crosswalk" style="color:#ff5e7c;"></i> Tentatives d'intrusion: {{ $tentativesIntrusion ?? 2 }} bloquées</div>
            </div>
            <hr style="border-color: #263f55; margin: 1.5rem 0;">
            <h4><i class="fas fa-exclamation-triangle"></i> Alertes récentes</h4>
            <div style="margin-top: 0.8rem;">
                @forelse($alertesRecente ?? [] as $alerte)
                    <div style="padding: 0.4rem 0; border-bottom: 1px solid #1d3347;">
                        <strong>{{ $alerte->nom_evenement ?? 'Alerte' }}</strong> — {{ Str::limit($alerte->description, 60) }}
                        <div style="font-size: 0.75rem; color: #8ba9d0;">{{ $alerte->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div>Aucune alerte récente.</div>
                @endforelse
            </div>
            <hr style="border-color: #263f55; margin: 1.5rem 0;">
            <h4><i class="fas fa-clock"></i> Sessions actives</h4>
            <div style="margin-top: 0.8rem;">
                @forelse($sessionsActives ?? [] as $session)
                <div><i class="fas fa-user"></i> {{ $session->user->name ?? 'admin' }} · {{ $session->ip_address ?? '10.0.0.15' }} · {{ $session->duree ?? '2h' }}</div>
                @empty
                <div><i class="fas fa-user"></i> admin · 10.0.0.15 · 2h</div>
                <div><i class="fas fa-user"></i> user_wifi · 10.0.0.112 · 35m</div>
                <div><i class="fas fa-user"></i> guest · 10.0.0.200 · 5m</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="quick-actions">
        <span class="chip"><i class="fas fa-play"></i> Redémarrer routeur</span>
        <span class="chip"><i class="fas fa-envelope"></i> Messagerie locale</span>
        <span class="chip"><i class="fas fa-chart-line"></i> Rapports</span>
        <span class="chip"><i class="fas fa-database"></i> Sauvegarde</span>
        <span class="chip"><i class="fas fa-wifi"></i> Scan WiFi</span>
    </div>

    <div class="footer">
        <i class="fas fa-shield-alt"></i> Connecté en tant que <strong>{{ Auth::user()->name ?? 'Admin' }}</strong> · 
        Rôle: <strong>{{ Auth::user()->roles->first()->name ?? 'superuser' }}</strong> · 
        Module messagerie sécurisée · Chiffrement AES-256 · MikroTik int.
    </div>
</div>
@endsection