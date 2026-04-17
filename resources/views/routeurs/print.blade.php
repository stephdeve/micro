@extends('layouts.app')

@section('title', 'Impression Routeurs')

@section('content')
<div class="main-content" style="padding: 1.5rem; min-height: auto;">
    <div class="card" style="background: #fff; color: #1f2937; border: none; box-shadow: none; padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <h1 style="margin: 0; font-size: 1.6rem;">Liste des routeurs</h1>
                <p style="margin: 0.35rem 0 0; color: #6b7280;">Impression générée depuis le backend</p>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="button" onclick="window.print()" class="btn-primary" style="background: #1c64f2; color: white; border: none; padding: 0.8rem 1.2rem; border-radius: 0.9rem;">Imprimer</button>
                <a href="{{ route('routeurs.index') }}" class="btn-secondary" style="padding: 0.8rem 1.2rem; border-radius: 0.9rem; text-decoration: none;">Retour</a>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; color: #111827;">
            <thead>
                <tr style="background: #f3f4f6; color: #111827; text-align: left; border-bottom: 2px solid #d1d5db;">
                    <th style="padding: 0.8rem; color: #111827;">Nom</th>
                    <th style="padding: 0.8rem; color: #111827;">Modèle</th>
                    <th style="padding: 0.8rem; color: #111827;">Adresse IP</th>
                    <th style="padding: 0.8rem; color: #111827;">Version ROS</th>
                    <th style="padding: 0.8rem; color: #111827;">Statut</th>
                    <th style="padding: 0.8rem; color: #111827;">Uptime</th>
                    <th style="padding: 0.8rem; color: #111827;">Emplacement</th>
                </tr>
            </thead>
            <tbody>
                @foreach($routeurs as $routeur)
                    <tr style="border-bottom: 1px solid #e5e7eb; color: #111827;">
                        <td style="padding: 0.8rem; color: #111827;">{{ $routeur->nom }}</td>
                        <td style="padding: 0.8rem; color: #111827;">{{ $routeur->modele ?? 'N/A' }}</td>
                        <td style="padding: 0.8rem; color: #111827;">{{ $routeur->adresse_ip }}</td>
                        <td style="padding: 0.8rem; color: #111827;">{{ $routeur->version_ros ?? 'N/A' }}</td>
                        <td style="padding: 0.8rem; color: #111827;">{{ ucfirst(str_replace('_', ' ', $routeur->statut)) }}</td>
                        <td style="padding: 0.8rem; color: #111827;">{{ $routeur->uptime ? floor($routeur->uptime / 86400) . ' jours' : 'N/A' }}</td>
                        <td style="padding: 0.8rem; color: #111827;">{{ $routeur->emplacement ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
    @media print {
        body, .main-content, .card {
            background: #fff !important;
            color: #111827 !important;
            box-shadow: none !important;
        }
        button, a.btn-secondary {
            display: none !important;
        }
    }
</style>
@endsection
