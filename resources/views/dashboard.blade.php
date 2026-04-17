@extends('layouts.app')

@section('title', __('Dashboard'))

@section('content')
<div class="main-content">
    @php
        $header_buttons = '<button class="btn-primary"><i class="fas fa-sync-alt"></i> ' . __('Synchronize') . '</button>';
    @endphp

    @include('layouts.guest')

    <!-- Dynamic statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-network-wired"></i> {{ __('Active routers') }}</div>
            <div class="stat-value">{{ $routeursActifs ?? 4 }}</div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> +1 {{ __('since yesterday') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-wifi"></i> {{ __('WiFi clients') }}</div>
            <div class="stat-value">{{ $clientsWiFi ?? 58 }}</div>
            <div class="stat-change"><i class="fas fa-arrow-down"></i> -3 {{ __('currently') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-envelope"></i> {{ __('Secure messages') }}</div>
            <div class="stat-value">{{ $messagesNonLus ?? 142 }}</div>
            <div class="stat-change"><i class="fas fa-clock"></i> {{ $messagesNonLus ?? 12 }} {{ __('unread') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-shield-alt"></i> {{ __('Network status') }}</div>
            <div class="stat-value" style="color: #2ef79b;">{{ $etatReseau ?? __('Secure') }}</div>
            <div class="stat-change"><i class="fas fa-check-circle"></i> {{ __('No incidents') }}</div>
        </div>
    </div>

    <!-- Section Routeur MikroTik -->
    <div class="router-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-microchip"></i> {{ $routeurPrincipal->nom ?? 'MikroTik RB951G-2HnD' }}</h3>
                <span class="status-badge">
                    <i class="fas fa-circle" style="color: {{ ($routeurPrincipal->statut ?? 'en_ligne') == 'en_ligne' ? '#2ef75b' : '#ff5e7c' }}; font-size: 0.6rem;"></i> 
                    {{ $routeurPrincipal->statut == 'en_ligne' ? __('Online') : ($routeurPrincipal->statut == 'maintenance' ? __('Maintenance') : __('Offline')) }}
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
                    <div class="interface-info"><span class="led-yellow"></span> <span>{{ __('No interfaces available') }}</span></div>
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

    <!-- Records table -->
    <div class="table-section">
        <div class="section-header">
            <h2><i class="fas fa-database" style="color: #4fc3ff;"></i> {{ __('Records management') }}</h2>
            <a href="{{ route('routeurs.index', ['create' => 1]) }}" class="btn-add"><i class="fas fa-plus"></i> {{ __('New record') }}</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Model') }}</th>
                        <th>{{ __('IP address') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Last seen') }}</th>
                        <th>{{ __('Actions') }}</th>
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
                                    {{ $enregistrement->statut == 'en_ligne' ? __('Online') : ($enregistrement->statut == 'maintenance' ? __('Maintenance') : __('Offline')) }}
                                </span>
                            </div>
                        </td>
                        <td>{{ $enregistrement->derniere_connexion ? $enregistrement->derniere_connexion->format('Y-m-d H:i') : 'N/A' }}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" onclick="window.location='{{ route('routeurs.show', $enregistrement) }}'" title="{{ __('View') }}"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" onclick="window.location='{{ route('routeurs.edit', $enregistrement) }}'" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('routeurs.destroy', $enregistrement) }}" method="POST" style="display: inline;" onsubmit="return confirm('{{ __('Confirm deletion?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="action-btn delete" type="submit" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td>Routeur Principal</td>
                        <td>MikroTik RB951G</td>
                        <td>192.168.1.1</td>
                        <td><div class="status-cell"><span class="status-active">{{ __('Active') }}</span></div></td>
                        <td>2024-01-15 14:32</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" title="{{ __('View') }}"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Switch Core</td>
                        <td>MikroTik CRS326</td>
                        <td>192.168.1.2</td>
                        <td><div class="status-cell"><span class="status-active">{{ __('Active') }}</span></div></td>
                        <td>2024-01-15 13:15</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" title="{{ __('View') }}"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="{{ __('Edit') }}"><i class="fas fa-edit"></i></button>
                                <button class="action-btn delete" title="{{ __('Delete') }}"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <i class="fas fa-shield-alt"></i> {{ __('Connected as') }} <strong>{{ Auth::user()->name ?? 'Admin' }}</strong> · 
        {{ __('Role') }}: <strong>{{ Auth::user()->roles->first()->name ?? 'superuser' }}</strong> · 
        {{ __('Secure messaging module') }} · {{ __('AES-256 encryption') }} · MikroTik int.
    </div>
</div>
@endsection