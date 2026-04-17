<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export PDF - Routeurs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            margin: 0;
            padding: 1.5rem;
        }
        .header {
            margin-bottom: 1rem;
        }
        .header h1 {
            margin: 0;
            font-size: 1.6rem;
        }
        .header p {
            margin: 0.35rem 0 0;
            color: #4b5563;
            font-size: 0.95rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.2rem;
            font-size: 0.92rem;
        }
        th, td {
            padding: 0.65rem 0.75rem;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        th {
            background: #f3f4f6;
            color: #111827;
            font-weight: 700;
        }
        td {
            color: #111827;
        }
        tr:nth-child(even) {
            background: #fafafa;
        }
        .status-en_ligne {
            color: #166534;
            font-weight: 700;
        }
        .status-hors_ligne {
            color: #9a3412;
            font-weight: 700;
        }
        .status-maintenance {
            color: #b45309;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Routeurs disponibles</h1>
        <p>Export PDF généré depuis le backend avec les mêmes colonnes que la vue Routeurs.</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Modèle</th>
                <th>Adresse IP</th>
                <th>Version ROS</th>
                <th>Statut</th>
                <th>Uptime</th>
            </tr>
        </thead>
        <tbody>
            @forelse($routeurs as $routeur)
                <tr>
                    <td>{{ $routeur->nom }}</td>
                    <td>{{ $routeur->modele ?? 'N/A' }}</td>
                    <td>{{ $routeur->adresse_ip }}</td>
                    <td>{{ $routeur->version_ros ?? 'N/A' }}</td>
                    <td class="status-{{ $routeur->statut }}">
                        {{ $routeur->statut == 'en_ligne' ? 'En ligne' : ($routeur->statut == 'maintenance' ? 'Maintenance' : 'Hors ligne') }}
                    </td>
                    <td>{{ $routeur->uptime ? floor($routeur->uptime / 86400) . ' jours' : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding: 1rem; color: #6b7280;">Aucun routeur trouvé</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
