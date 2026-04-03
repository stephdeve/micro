@extends('layouts.app')

@section('title', "Détail interface")

@php
    $header_buttons = '<a href="'.route('interfaces.index').'" class="btn-icon" style="width: auto; padding: 0 1.5rem; border-radius: 2rem;"><i class="fas fa-arrow-left"></i> Retour</a>';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg" style="display:none;"></div>

    @include('layouts.guest')

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-header">
            <h2><i class="fas fa-network-wired"></i> Interface {{ $interface->nom }}</h2>
            <span class="status-{{ $interface->statut == 'actif' ? 'active' : ($interface->statut == 'erreur' ? 'inactive' : 'inactive') }}">{{ ucfirst($interface->statut) }}</span>
        </div>

        <div style="padding: 1.5rem;">
            <table style="width: 100%;"> 
                <tr><td style="color:#8ba9d0;">Routeur</td><td>{{ $interface->routeur->nom ?? 'N/A' }}</td></tr>
                <tr><td style="color:#8ba9d0;">Type</td><td>{{ ucfirst($interface->type) }}</td></tr>
                <tr><td style="color:#8ba9d0;">Adresse MAC</td><td>{{ $interface->adresse_mac ?? 'N/A' }}</td></tr>
                <tr><td style="color:#8ba9d0;">Adresse IP</td><td>{{ $interface->adresse_ip ?? 'N/A' }}</td></tr>
                <tr><td style="color:#8ba9d0;">Uptime / débit</td><td>{{ $interface->debit_entrant ?? 0 }}/{{ $interface->debit_sortant ?? 0 }} Mbps</td></tr>
                <tr><td style="color:#8ba9d0;">VLAN</td><td>{{ $interface->vlan_id ?? 'N/A' }}</td></tr>
                <tr><td style="color:#8ba9d0;">Description</td><td>{{ $interface->description ?? 'Aucune description' }}</td></tr>
            </table>

            <div style="margin-top: 1.5rem; display:flex; gap: 0.8rem;">
                <a class="btn-add" href="{{ route('interfaces.edit', $interface) }}"><i class="fas fa-edit"></i> Modifier</a>
                <form method="POST" action="{{ route('interfaces.destroy', $interface) }}" onsubmit="return confirm('Supprimer cette interface ?');">
                    @csrf
                    @method('DELETE')
                    <button class="btn-danger" type="submit"><i class="fas fa-trash"></i> Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection