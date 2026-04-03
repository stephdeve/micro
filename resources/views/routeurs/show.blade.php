@extends('layouts.app')

@section('title', 'Détail Routeur')

@php
    $header_buttons = '<a href="'.route('routeurs.index').'" class="btn-icon" style="width: auto; padding: 0 1.5rem; border-radius: 2rem;"><i class="fas fa-arrow-left"></i> Retour</a>';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <div style="max-width: 1000px; margin: 0 auto;">
        <!-- En-tête -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap;">
            <div>
                <h2 style="font-size: 2rem; color: white;">{{ $routeur->nom }}</h2>
                <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                    <span class="status-{{ $routeur->statut == 'en_ligne' ? 'active' : 'inactive' }}">
                        {{ $routeur->statut == 'en_ligne' ? 'En ligne' : ($routeur->statut == 'maintenance' ? 'Maintenance' : 'Hors ligne') }}
                    </span>
                    <span style="background: #1f3a4b; padding: 0.3rem 1rem; border-radius: 30px; border: 1px solid #2f93b0; color: #a2ecff;">
                        <i class="fas fa-microchip"></i> {{ $routeur->modele ?? 'N/A' }}
                    </span>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button class="btn-primary" onclick="window.location='{{ route('routeurs.edit', $routeur) }}'">
                    <i class="fas fa-edit"></i> Modifier
                </button>
                <button class="btn-primary" onclick="syncRouteur({{ $routeur->id }})">
                    <i class="fas fa-sync-alt"></i> Synchroniser
                </button>
            </div>
        </div>

        <!-- Informations générales -->
        <div class="router-section" style="grid-template-columns: 1fr 1fr;">
            <div class="card">
                <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-info-circle"></i> Informations générales</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Adresse IP</td>
                        <td style="padding: 0.5rem 0;"><strong>{{ $routeur->adresse_ip }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Adresse MAC</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->adresse_mac ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Numéro de série</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->numero_serie ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Version RouterOS</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->version_ros ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Firmware</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->firmware ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Emplacement</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->emplacement ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Responsable</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->responsable->name ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <div class="card">
                <h3 style="margin-bottom: 1.5rem;"><i class="fas fa-chart-line"></i> Performances</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Uptime</td>
                        <td style="padding: 0.5rem 0;">
                            @if($routeur->uptime)
                                {{ floor($routeur->uptime / 86400) }} jours {{ floor(($routeur->uptime % 86400) / 3600) }} heures
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">CPU</td>
                        <td style="padding: 0.5rem 0;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span>{{ $routeur->cpu_usage ?? 0 }}%</span>
                                <div style="flex: 1; height: 8px; background: #1a2c3c; border-radius: 4px;">
                                    <div style="width: {{ $routeur->cpu_usage ?? 0 }}%; height: 8px; background: linear-gradient(90deg, #00ccff, #904eff); border-radius: 4px;"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Mémoire</td>
                        <td style="padding: 0.5rem 0;">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span>{{ $routeur->memory_usage ?? 0 }}%</span>
                                <div style="flex: 1; height: 8px; background: #1a2c3c; border-radius: 4px;">
                                    <div style="width: {{ $routeur->memory_usage ?? 0 }}%; height: 8px; background: linear-gradient(90deg, #2ef75b, #00ccff); border-radius: 4px;"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Température</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->temperature ?? 'N/A' }} °C</td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Dernière connexion</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->derniere_connexion ? $routeur->derniere_connexion->format('d/m/Y H:i') : 'Jamais' }}</td>
                    </tr>
                    <tr>
                        <td style="color: #8ba9d0; padding: 0.5rem 0;">Dernière synchronisation</td>
                        <td style="padding: 0.5rem 0;">{{ $routeur->derniere_sync ? $routeur->derniere_sync->format('d/m/Y H:i') : 'Jamais' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Description -->
        @if($routeur->description)
        <div class="card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1rem;"><i class="fas fa-align-left"></i> Description</h3>
            <p style="color: #cddfff; line-height: 1.6;">{{ $routeur->description }}</p>
        </div>
        @endif

        <!-- Interfaces -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3><i class="fas fa-network-wired"></i> Interfaces ({{ $routeur->interfaces->count() }})</h3>
                <button class="btn-add" onclick="window.location='{{ route('interfaces.create', ['routeur_id' => $routeur->id]) }}'">
                    <i class="fas fa-plus"></i> Ajouter une interface
                </button>
            </div>

            @if($routeur->interfaces->count() > 0)
            <div class="table-container" style="margin-top: 1rem;">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Adresse IP</th>
                            <th>MAC</th>
                            <th>Statut</th>
                            <th>Débit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routeur->interfaces as $interface)
                        <tr>
                            <td>{{ $interface->nom }}</td>
                            <td>{{ $interface->type }}</td>
                            <td>{{ $interface->adresse_ip ?? 'N/A' }}</td>
                            <td>{{ $interface->adresse_mac ?? 'N/A' }}</td>
                            <td>
                                <span class="status-{{ $interface->statut == 'actif' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($interface->statut) }}
                                </span>
                            </td>
                            <td>{{ $interface->debit_entrant ?? 0 }}/{{ $interface->debit_sortant ?? 0 }} Mbps</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn view" title="Voir"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn edit" title="Modifier"><i class="fas fa-edit"></i></button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="text-align: center; padding: 3rem; color: #8ba9d0;">
                <i class="fas fa-ethernet" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                <p>Aucune interface configurée</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function syncRouteur(id) {
    if (confirm('Lancer la synchronisation de ce routeur ?')) {
        window.location.href = `/routeurs/${id}/sync`;
    }
}
</script>
@endsection