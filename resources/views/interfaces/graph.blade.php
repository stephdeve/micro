@extends('layouts.app')

@section('title', 'Graphique interface')

@php
    $header_buttons = '';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <div class="router-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line"></i> Graphiques de trafic pour {{ $interface->nom }}</h3>
                <a href="{{ route('interfaces.index') }}" class="btn-add"><i class="fas fa-arrow-left"></i> Retour</a>
            </div>

            <div style="padding: 1rem;">
                <p><strong>Routeur :</strong> {{ $interface->routeur->nom ?? 'N/A' }} ({{ $interface->routeur->adresse_ip ?? 'N/A' }})</p>
                <p><strong>Statut :</strong> {{ ucfirst($interface->statut) }} | <strong>Débit :</strong> {{ number_format($interface->debit_entrant, 1) }} / {{ number_format($interface->debit_sortant, 1) }} Mbps</p>

                <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                    <thead>
                        <tr style="background: #12223c; color: #b8ccf7;">
                            <th style="padding: 0.6rem; border: 1px solid #1c3349;">Heure</th>
                            <th style="padding: 0.6rem; border: 1px solid #1c3349;">Rx (Mbps)</th>
                            <th style="padding: 0.6rem; border: 1px solid #1c3349;">Tx (Mbps)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historique as $row)
                        <tr style="border: 1px solid #1c3349;">
                            <td style="padding: 0.5rem;">{{ $row['heure'] }}</td>
                            <td style="padding: 0.5rem;">{{ $row['debit_entrant'] }}</td>
                            <td style="padding: 0.5rem;">{{ $row['debit_sortant'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    @foreach($historique as $row)
                        <div style="flex: 1; text-align: center;">
                            <div style="width: 100%; height: 120px; background: linear-gradient(to top, #1a2c3c, #0f1a24); border-radius: 0.75rem; position: relative;">
                                <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: {{ min(100, max(0, round(($row['debit_entrant'] + $row['debit_sortant']) / 2, 0))) }}%; background: linear-gradient(90deg, #00ccff, #2ef75b); border-radius: 0.75rem 0.75rem 0 0;"></div>
                            </div>
                            <span style="display: block; margin-top: 0.4rem; color: #9ec5ef;">{{ $row['heure'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection