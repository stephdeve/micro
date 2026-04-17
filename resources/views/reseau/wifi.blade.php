@extends('layouts.app')

@section('title', 'WiFi - ' . $routeur->nom)

@section('content')
<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-wifi"></i> Configuration WiFi</h1>
        <p>Routeur : {{ $routeur->nom }} ({{ $routeur->adresse_ip }})</p>
    </div>

    <div class="dashboard-grid">
        <!-- Interfaces WiFi -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-broadcast-tower"></i> Interfaces WiFi</h3>
            </div>
            <div class="card-body">
                @forelse($interfaces ?? [] as $iface)
                    <div class="wifi-interface {{ $iface['disabled'] ? 'disabled' : '' }}">
                        <div class="wifi-header">
                            <h4>{{ $iface['name'] }}</h4>
                            <span class="status-badge {{ $iface['disabled'] ? 'status-off' : 'status-on' }}">
                                {{ $iface['disabled'] ? 'Désactivé' : 'Actif' }}
                            </span>
                        </div>
                        <div class="wifi-details">
                            <div class="detail-item">
                                <label>SSID:</label>
                                <strong>{{ $iface['ssid'] }}</strong>
                            </div>
                            <div class="detail-item">
                                <label>Fréquence:</label>
                                <span>{{ $iface['frequency'] ?? '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Band:</label>
                                <span>{{ $iface['band'] ?? '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Largeur canal:</label>
                                <span>{{ $iface['channel_width'] ?? '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Mode:</label>
                                <span>{{ $iface['mode'] ?? '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <label>Profil sécurité:</label>
                                <span>{{ $iface['security_profile'] ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="wifi-actions">
                            <input type="text" id="new-ssid-{{ $iface['id'] }}" placeholder="Nouveau SSID" class="form-input">
                            <button class="btn btn-primary" onclick="updateSsid('{{ $iface['id'] }}')">
                                <i class="fas fa-save"></i> Modifier SSID
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Aucune interface WiFi détectée.</p>
                @endforelse
            </div>
        </div>

        <!-- Clients connectés -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-laptop"></i> Clients connectés</h3>
                <span class="badge badge-count">{{ count($clients ?? []) }}</span>
            </div>
            <div class="card-body">
                @forelse($clients ?? [] as $client)
                    <div class="client-item">
                        <div class="client-mac">{{ $client['mac_address'] }}</div>
                        <div class="client-details">
                            <span>{{ $client['hostname'] ?? 'Inconnu' }}</span>
                            <span class="signal-badge">
                                <i class="fas fa-signal"></i> {{ $client['signal_strength'] ?? '—' }}
                            </span>
                        </div>
                        <div class="client-meta">
                            <small>Interface: {{ $client['interface'] }}</small>
                            <small>TX: {{ $client['tx_rate'] ?? '—' }}</small>
                            <small>RX: {{ $client['rx_rate'] ?? '—' }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Aucun client connecté.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function updateSsid(interfaceId) {
        const ssid = document.getElementById(`new-ssid-${interfaceId}`).value;
        if (!ssid) return alert('Veuillez entrer un SSID');
        
        fetch('{{ route('admin-reseau.wifi.ssid', $routeur) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ interface_id: interfaceId, ssid: ssid })
        }).then(r => r.json()).then(d => {
            if (d.success) alert('SSID modifié avec succès');
            else alert('Erreur lors de la modification');
        });
    }
</script>

<style>
    .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 768px) { .dashboard-grid { grid-template-columns: 1fr; } }
    .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
    .card-header { padding: 1rem 1.2rem; border-bottom: 1px solid #eee; background: #f8f9fa; display: flex; justify-content: space-between; align-items: center; }
    .card-header h3 { margin: 0; font-size: 1rem; }
    .card-body { padding: 1.2rem; }
    .wifi-interface { border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
    .wifi-interface.disabled { opacity: 0.6; background: #f5f5f5; }
    .wifi-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.8rem; }
    .wifi-header h4 { margin: 0; color: #2c3e50; }
    .status-badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
    .status-on { background: #d4edda; color: #155724; }
    .status-off { background: #f8d7da; color: #721c24; }
    .wifi-details { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem; }
    .detail-item { display: flex; justify-content: space-between; padding: 0.3rem 0; border-bottom: 1px dashed #eee; }
    .detail-item label { color: #6c757d; font-size: 0.85rem; }
    .wifi-actions { display: flex; gap: 0.5rem; }
    .form-input { flex: 1; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
    .btn { padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; }
    .btn-primary { background: #3498db; color: #fff; }
    .badge { padding: 0.2rem 0.5rem; border-radius: 4px; }
    .badge-count { background: #3498db; color: #fff; }
    .client-item { padding: 0.8rem; border-bottom: 1px solid #eee; }
    .client-mac { font-family: monospace; font-weight: 600; color: #2c3e50; }
    .client-details { display: flex; justify-content: space-between; margin: 0.3rem 0; }
    .signal-badge { color: #27ae60; }
    .client-meta { display: flex; gap: 1rem; color: #6c757d; font-size: 0.8rem; }
    .text-muted { color: #6c757d; }
</style>
@endsection
