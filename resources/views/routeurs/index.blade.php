@extends('layouts.app')

@section('title', 'Routeurs')

@php
    $header_buttons = '';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <!-- Statistiques routeurs -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-check-circle" style="color: #2ef75b;"></i> Routeurs en ligne</div>
            <div class="stat-value" data-value="{{ $stats['en_ligne'] }}">{{ number_format($stats['en_ligne'], 0, ',', ' ') }}</div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> Actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-exclamation-triangle" style="color: #ffaa33;"></i> Routeurs hors ligne</div>
            <div class="stat-value" data-value="{{ $stats['hors_ligne'] }}">{{ number_format($stats['hors_ligne'], 0, ',', ' ') }}</div>
            <div class="stat-change"><i class="fas fa-exclamation-circle"></i> Attention requise</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-microchip"></i> En maintenance</div>
            <div class="stat-value" data-value="{{ $stats['maintenance'] }}">{{ number_format($stats['maintenance'], 0, ',', ' ') }}</div>
            <div class="stat-change">{{ $stats['modeles'] }} modèles différents</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-version"></i> Firmware</div>
            <div class="stat-value">{{ $stats['derniere_version'] }}</div>
            <div class="stat-change">Dernière version</div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="filters-section">
        <form method="GET" action="{{ route('routeurs.index') }}" id="filter-form">
            <div class="filters-controls">
                <div style="flex: 1; min-width: 300px;">
                    <input type="text" name="search" class="input-field" placeholder="Rechercher un routeur (IP, nom, modèle...)" value="{{ request('search') }}" style="width: 100%;">
                </div>
                <select name="statut" class="input-field" style="width: auto; min-width: 150px;" onchange="document.getElementById('filter-form').submit()">
                    <option value="">Tous les statuts</option>
                    <option value="en_ligne" {{ request('statut') == 'en_ligne' ? 'selected' : '' }}>En ligne</option>
                    <option value="hors_ligne" {{ request('statut') == 'hors_ligne' ? 'selected' : '' }}>Hors ligne</option>
                    <option value="maintenance" {{ request('statut') == 'maintenance' ? 'selected' : '' }}>En maintenance</option>
                </select>
                <select name="modele" class="input-field" style="width: auto; min-width: 150px;" onchange="document.getElementById('filter-form').submit()">
                    <option value="">Tous les modèles</option>
                    @foreach($modeles as $modele)
                        <option value="{{ $modele }}" {{ request('modele') == $modele ? 'selected' : '' }}>{{ $modele }}</option>
                    @endforeach
                </select>
                @if(request()->anyFilled(['search', 'statut', 'modele']))
                    <a href="{{ route('routeurs.index') }}" class="btn-secondary" style="width: auto; padding: 0 1.5rem;">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Liste des routeurs -->
    <div class="table-section">
        <div class="section-header">
            <h2><i class="fas fa-list"></i> Routeurs disponibles</h2>
            <div style="display: flex; gap: 1rem;">
                <button class="btn-add" onclick="openModal('add')">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
                <button class="btn-add"><i class="fas fa-file-export"></i> Exporter</button>
                <button class="btn-add"><i class="fas fa-print"></i> Imprimer</button>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Modèle</th>
                        <th>Adresse IP</th>
                        <th>Version ROS</th>
                        <th>Statut</th>
                        <th>Uptime</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($routeurs as $routeur)
                    <tr>
                        <td>
                            <i class="fas fa-{{ $routeur->statut == 'en_ligne' ? 'check-circle' : ($routeur->statut == 'maintenance' ? 'tools' : 'exclamation-circle') }}" 
                               style="color: {{ $routeur->statut == 'en_ligne' ? '#2ef75b' : ($routeur->statut == 'maintenance' ? '#ffaa33' : '#ff5e7c') }};"></i> 
                            {{ $routeur->nom }}
                        </td>
                        <td>{{ $routeur->modele ?? 'N/A' }}</td>
                        <td>{{ $routeur->adresse_ip }}</td>
                        <td>{{ $routeur->version_ros ?? 'N/A' }}</td>
                        <td style="white-space: nowrap;">
                            <span class="status-{{ $routeur->statut == 'en_ligne' ? 'active' : ($routeur->statut == 'maintenance' ? 'inactive' : 'inactive') }}">
                                {{ $routeur->statut == 'en_ligne' ? 'En ligne' : ($routeur->statut == 'maintenance' ? 'Maintenance' : 'Hors ligne') }}
                            </span>
                        </td>
                        <td>
                            @if($routeur->uptime)
                                {{ floor($routeur->uptime / 86400) }} jours
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" onclick="window.location='{{ route('routeurs.show', $routeur) }}'" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" onclick="editRouteur({{ $routeur->id }})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- <button class="action-btn" style="background:#1f3145;" onclick="syncRouteur({{ $routeur->id }})" title="Synchroniser">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="action-btn" style="background:#1f3145;" title="Console">
                                    <i class="fas fa-terminal"></i>
                                </button> -->
                                <button class="action-btn delete" onclick="deleteRouteur({{ $routeur->id }})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="no-data">
                            <i class="fas fa-router"></i>
                            <p>Aucun routeur trouvé</p>
                            <button class="btn-primary" onclick="openModal('add')" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Ajouter un routeur
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($routeurs->hasPages())
        <style>
            .pagination-btn {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                border: 1px solid #3a5578;
                background: #0f1f35;
                color: #8fa5bd;
                display: inline-flex;
                justify-content: center;
                align-items: center;
                text-decoration: none;
                font-size: 0.9rem;
                cursor: pointer;
                transition: all 0.2s ease;
                padding: 0;
            }
            .pagination-btn:hover {
                background: #1a2d45;
                border-color: #5a7fa0;
                color: #b9d1e8;
            }
            .pagination-btn.active {
                background: #1e3a5d;
                border-color: #4a8fd4;
                color: #fff;
                font-weight: 700;
                box-shadow: 0 0 10px rgba(74, 143, 212, 0.25);
            }
        </style>

        <div style="margin-top: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="color: #b9cee5; font-size: 0.88rem;">
                Page <strong>{{ $routeurs->currentPage() }}</strong> / {{ $routeurs->lastPage() }}  •  <strong>{{ $routeurs->total() }}</strong> routeur{{ $routeurs->total() > 1 ? 's' : '' }}
            </div>
            <div id="routeursPaginationContainer" style="display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center;">
                @php
                    $from = max(1, $routeurs->currentPage() - 2);
                    $to = min($routeurs->lastPage(), $routeurs->currentPage() + 2);
                @endphp

                @if($from > 1)
                    <button class="pagination-btn" onclick="loadPage(1)">1</button>
                @endif

                @if($from > 2)
                    <span style="color: #6b7f96; padding: 0 0.3rem;">…</span>
                @endif

                @for($page = $from; $page <= $to; $page++)
                    @if($page === $routeurs->currentPage())
                        <span class="pagination-btn active">{{ $page }}</span>
                    @else
                        <button class="pagination-btn" onclick="loadPage({{ $page }})">{{ $page }}</button>
                    @endif
                @endfor

                @if($to < $routeurs->lastPage() - 1)
                    <span style="color: #6b7f96; padding: 0 0.3rem;">…</span>
                @endif

                @if($to < $routeurs->lastPage())
                    <button class="pagination-btn" onclick="loadPage({{ $routeurs->lastPage() }})">{{ $routeurs->lastPage() }}</button>
                @endif
            </div>
        </div>

        <script>
            function loadPage(page) {
                const params = new URLSearchParams(window.location.search);
                params.set('page', page);
                
                fetch(`{{ route('routeurs.index') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Remplacer le tbody
                    const oldTbody = document.querySelector('tbody');
                    const newTbody = doc.querySelector('tbody');
                    if (oldTbody && newTbody) {
                        oldTbody.replaceWith(newTbody);
                    }
                    
                    // Remplacer la pagination
                    const oldPagination = document.getElementById('routeursPaginationContainer');
                    const newPagination = doc.getElementById('routeursPaginationContainer');
                    if (oldPagination && newPagination) {
                        oldPagination.replaceWith(newPagination);
                    }
                    
                    // Scroll vers le tableau
                    document.querySelector('.table-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Mettre à jour l'URL
                    window.history.pushState(null, '', `{{ route('routeurs.index') }}?${params.toString()}`);
                })
                .catch(error => console.error('Erreur chargement:', error));
            }
        </script>
        @endif
    </div>

    <!-- Carte réseau -->
    <div class="router-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-project-diagram"></i> Topologie réseau</h3>
                <span class="status-badge">Vue simplifiée</span>
            </div>
            <svg width="100%" height="320" viewBox="0 0 600 320" style="background: linear-gradient(135deg, #0f1a24 0%, #1a2540 100%); border-radius: 0.8rem;">
                <defs>
                    <style>
                        @keyframes pulse-green { 0%, 100% { r: 28; } 50% { r: 32; } }
                        @keyframes pulse-orange { 0%, 100% { r: 28; } 50% { r: 32; } }
                        @keyframes pulse-red { 0%, 100% { r: 28; } 50% { r: 32; } }
                        .router-online { animation: pulse-green 2s infinite; }
                        .router-maintenance { animation: pulse-orange 2s infinite; }
                        .router-offline { animation: pulse-red 2s infinite; }
                    </style>
                    <radialGradient id="grad-online" cx="40%" cy="40%">
                        <stop offset="0%" style="stop-color:#5eff9b;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#2ef75b;stop-opacity:1" />
                    </radialGradient>
                    <radialGradient id="grad-maintenance" cx="40%" cy="40%">
                        <stop offset="0%" style="stop-color:#ffd166;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#ffaa33;stop-opacity:1" />
                    </radialGradient>
                    <radialGradient id="grad-offline" cx="40%" cy="40%">
                        <stop offset="0%" style="stop-color:#ff9fa0;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#ff5e7c;stop-opacity:1" />
                    </radialGradient>
                    <radialGradient id="hub-grad" cx="40%" cy="40%">
                        <stop offset="0%" style="stop-color:#73e8ff;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#00a6ff;stop-opacity:1" />
                    </radialGradient>
                </defs>
                
                <!-- Ligne d'arrière-plan (grille subtile) -->
                <g stroke="rgba(79, 195, 255, 0.1)" stroke-width="1">
                    <line x1="50%" y1="0" x2="50%" y2="100%"/>
                    <line x1="0" y1="50%" x2="100%" y2="50%"/>
                </g>
                
                <!-- Hub central -->
                <circle cx="300" cy="160" r="28" fill="url(#hub-grad)" stroke="#00e5ff" stroke-width="2" filter="drop-shadow(0 0 12px #00a6ff)"/>
                <text x="300" y="168" text-anchor="middle" fill="white" font-size="18" font-weight="bold">🌐</text>
                
                <!-- Routeurs -->
                @php
                    $routerCount = max(count($routeurs ?? []), 1);
                    $radius = 120;
                @endphp
                
                @foreach(($routeurs ?? []) as $index => $routeur)
                    @php
                        $angle = (360 / $routerCount) * $index - 90;
                        $rad = deg2rad($angle);
                        $cx = 300 + ($radius * cos($rad));
                        $cy = 160 + ($radius * sin($rad));
                        
                        if ($routeur->statut == 'en_ligne') {
                            $grad = 'grad-online';
                            $glow = 'drop-shadow(0 0 18px #2ef75b)';
                            $class = 'router-online';
                        } elseif ($routeur->statut == 'maintenance') {
                            $grad = 'grad-maintenance';
                            $glow = 'drop-shadow(0 0 18px #ffaa33)';
                            $class = 'router-maintenance';
                        } else {
                            $grad = 'grad-offline';
                            $glow = 'drop-shadow(0 0 18px #ff5e7c)';
                            $class = 'router-offline';
                        }
                    @endphp
                    
                    <!-- Ligne vers le hub -->
                    <line x1="300" y1="160" x2="{{ $cx }}" y2="{{ $cy }}" stroke="{{ $routeur->statut == 'en_ligne' ? '#2ef75b' : ($routeur->statut == 'maintenance' ? '#ffaa33' : '#ff5e7c') }}" stroke-width="2" opacity="0.4" stroke-dasharray="5,5"/>
                    
                    <!-- Nœud routeur animé -->
                    <circle cx="{{ $cx }}" cy="{{ $cy }}" r="28" fill="url(#{{ $grad }})" stroke="{{ $routeur->statut == 'en_ligne' ? '#5eff9b' : ($routeur->statut == 'maintenance' ? '#ffd166' : '#ff9fa0') }}" stroke-width="2.5" filter="{{ $glow }}" class="{{ $class }}"/>
                    
                    <!-- Icône routeur -->
                    <text x="{{ $cx }}" y="{{ $cy + 7 }}" text-anchor="middle" fill="white" font-size="16" font-weight="bold">📡</text>
                    
                    <!-- Label routeur -->
                    <text x="{{ $cx }}" y="{{ $cy + 48 }}" text-anchor="middle" fill="white" font-size="10" font-weight="600" opacity="0.9">
                        {{ substr($routeur->nom, 0, 10) }}
                    </text>
                    <text x="{{ $cx }}" y="{{ $cy + 62 }}" text-anchor="middle" fill="{{ $routeur->statut == 'en_ligne' ? '#2ef75b' : ($routeur->statut == 'maintenance' ? '#ffaa33' : '#ff5e7c') }}" font-size="9" font-weight="500">
                        {{ $routeur->statut == 'en_ligne' ? '● En ligne' : ($routeur->statut == 'maintenance' ? '● Maintenance' : '● Hors ligne') }}
                    </text>
                @endforeach
                
                <!-- Légende -->
                <g transform="translate(10, 290)">
                    <rect x="0" y="0" width="200" height="25" fill="rgba(0,0,0,0.3)" rx="4" stroke="rgba(79, 195, 255, 0.2)" stroke-width="1"/>
                    <circle cx="10" cy="12" r="4" fill="#2ef75b"/>
                    <text x="20" y="16" fill="white" font-size="9">En ligne</text>
                    
                    <circle cx="70" cy="12" r="4" fill="#ffaa33"/>
                    <text x="80" y="16" fill="white" font-size="9">Maintenance</text>
                    
                    <circle cx="160" cy="12" r="4" fill="#ff5e7c"/>
                    <text x="170" y="16" fill="white" font-size="9">Hors ligne</text>
                </g>
            </svg>
        </div>        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line"></i> Performance globale</h3>
                <i class="fas fa-ellipsis-h"></i>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="margin-bottom: 1.8rem;">
                    <div style="display: flex; justify-content: space-between; color: #aaccff; margin-bottom: 0.5rem; font-weight: 500;">
                        <span>Charge CPU moyenne</span> 
                        <span class="perf-value-cpu" data-value="{{ $globalPerformance['cpu'] ?? 0 }}">{{ number_format($globalPerformance['cpu'] ?? 0, 1, ',', ' ') }}%</span>
                    </div>
                    <div style="height: 12px; background: #1a2c3c; border-radius: 20px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);">
                        <div class="perf-bar-cpu" data-max="100" style="width: 0%; height: 12px; background: linear-gradient(90deg, #00ccff, #904eff); border-radius: 20px; transition: width 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94); box-shadow: 0 0 12px rgba(144, 78, 255, 0.6);"></div>
                    </div>
                </div>
                
                <div style="margin-bottom: 1.8rem;">
                    <div style="display: flex; justify-content: space-between; color: #aaccff; margin-bottom: 0.5rem; font-weight: 500;">
                        <span>Mémoire utilisée</span> 
                        <span class="perf-value-mem" data-value="{{ $globalPerformance['memory'] ?? 0 }}">{{ number_format($globalPerformance['memory'] ?? 0, 1, ',', ' ') }}%</span>
                    </div>
                    <div style="height: 12px; background: #1a2c3c; border-radius: 20px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);">
                        <div class="perf-bar-mem" data-max="100" style="width: 0%; height: 12px; background: linear-gradient(90deg, #00ccff, #2ef75b); border-radius: 20px; transition: width 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94); box-shadow: 0 0 12px rgba(46, 247, 91, 0.6);"></div>
                    </div>
                </div>
                
                <div style="margin-bottom: 0.8rem;">
                    <div style="display: flex; justify-content: space-between; color: #aaccff; margin-bottom: 0.5rem; font-weight: 500;">
                        <span>Température moyenne</span> 
                        <span class="perf-value-temp" data-value="{{ $globalPerformance['temperature'] ?? 0 }}">{{ number_format($globalPerformance['temperature'] ?? 0, 1, ',', ' ') }}°C</span>
                    </div>
                    <div style="height: 12px; background: #1a2c3c; border-radius: 20px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.5);">
                        <div class="perf-bar-temp" data-max="100" style="width: 0%; height: 12px; background: linear-gradient(90deg, #2ef75b, #ffaa33); border-radius: 20px; transition: width 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94); box-shadow: 0 0 12px rgba(255, 170, 51, 0.6);"></div>
                    </div>
                </div>
            </div>
            <div style="border-top: 1px solid rgba(110, 166, 228, 0.18); padding-top: 0.8rem; color: #bde4ff;">
                <strong>Total bande passante :</strong> {{ number_format($totalBandwidth, 0, ',', ' ') }} Mbps
            </div>
            <div style="margin-top: 1rem; color: #d6e8ff;">
                <strong>Top consommateurs</strong>
                <ul style="margin: 0.6rem 0 0; padding-left: 1.1rem; list-style: disc;">
                    @forelse($topConsumers as $consumer)
                        <li>{{ $consumer['routeur'] }} / {{ $consumer['interface'] }} : {{ number_format($consumer['total'], 0, ',', ' ') }} Mbps</li>
                    @empty
                        <li>Aucune donnée disponible</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- MODAL AJOUTER/MODIFIER UN ROUTEUR -->
<div id="routeurModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #132231; border-radius: 2rem; padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; border: 1px solid #2e4b6b;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 id="modalTitle" style="color: white;"><i class="fas fa-plus-circle"></i> Ajouter un routeur</h3>
            <button onclick="closeModal()" style="background: none; border: none; color: #8ba9d0; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form id="routeurForm" method="POST" action="{{ route('routeurs.store') }}">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Nom *</label>
                <input type="text" name="nom" id="nom" class="input-field" required>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Modèle</label>
                <input type="text" name="modele" id="modele" class="input-field">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Adresse IP *</label>
                <input type="text" name="adresse_ip" id="adresse_ip" class="input-field" required>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Adresse MAC</label>
                <input type="text" name="adresse_mac" id="adresse_mac" class="input-field" placeholder="00:11:22:33:44:55">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Version ROS</label>
                    <input type="text" name="version_ros" id="version_ros" class="input-field">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Firmware</label>
                    <input type="text" name="firmware" id="firmware" class="input-field">
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Numéro de série</label>
                <input type="text" name="numero_serie" id="numero_serie" class="input-field">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Statut *</label>
                <select name="statut" id="statut" class="input-field" required>
                    <option value="en_ligne">En ligne</option>
                    <option value="hors_ligne">Hors ligne</option>
                    <option value="maintenance">En maintenance</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Emplacement</label>
                <input type="text" name="emplacement" id="emplacement" class="input-field" placeholder="Salle serveur, armoire...">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Description</label>
                <textarea name="description" id="description" class="input-field" rows="3"></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
                <button type="button" onclick="closeModal()" class="btn-icon" style="width: auto; padding: 0 1.5rem; border-radius: 2rem;">
                    <i class="fas fa-times"></i> Annuler
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Script routeurs chargé');
    
    const modal = document.getElementById('routeurModal');
    console.log('🔍 Modal trouvé:', modal ? 'Oui' : 'Non');

    // Fonction pour ouvrir le modal
    window.openModal = function(action, id = null) {
        console.log('🖱️ openModal appelé', action, id);
        
        if (!modal) {
            console.error('❌ Modal non trouvé!');
            return;
        }

        const form = document.getElementById('routeurForm');
        const title = document.getElementById('modalTitle');
        const methodInput = document.getElementById('method');

        if (action === 'add') {
            console.log('📝 Mode ajout');
            title.innerHTML = '<i class="fas fa-plus-circle"></i> Ajouter un routeur';
            form.action = "{{ route('routeurs.store') }}";
            methodInput.value = 'POST';
            form.reset();
        } else if (action === 'edit' && id) {
            console.log('📝 Mode édition', id);
            title.innerHTML = '<i class="fas fa-edit"></i> Modifier le routeur';
            form.action = "{{ url('routeurs') }}/" + id;
            methodInput.value = 'PUT';
            
            // Charger les données
            fetch(`/routeurs/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    console.log('📊 Données reçues:', data);
                    document.getElementById('nom').value = data.nom || '';
                    document.getElementById('modele').value = data.modele || '';
                    document.getElementById('adresse_ip').value = data.adresse_ip || '';
                    document.getElementById('adresse_mac').value = data.adresse_mac || '';
                    document.getElementById('version_ros').value = data.version_ros || '';
                    document.getElementById('firmware').value = data.firmware || '';
                    document.getElementById('numero_serie').value = data.numero_serie || '';
                    document.getElementById('statut').value = data.statut || 'en_ligne';
                    document.getElementById('emplacement').value = data.emplacement || '';
                    document.getElementById('description').value = data.description || '';
                })
                .catch(error => {
                    console.error('❌ Erreur:', error);
                    alert('Impossible de charger les données du routeur');
                });
        }

        modal.style.display = 'flex';
    };

    window.closeModal = function() {
        console.log('🔒 Fermeture du modal');
        if (modal) {
            modal.style.display = 'none';
        }
    };

    window.editRouteur = function(id) {
        openModal('edit', id);
    };
    // Intercepter la soumission du formulaire Routeur pour fetch + notifications instantanées
    const routeurForm = document.getElementById('routeurForm');
    if (routeurForm) {
        routeurForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(routeurForm);
            const action = routeurForm.action;
            const method = document.getElementById('method').value;

            try {
                const response = await fetch(action, {
                    method: method === 'POST' ? 'POST' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw result;
                }

                alert(result.message || 'Routeur sauvegardé !');
                closeModal();

                if (window.refreshNotifications) {
                    window.refreshNotifications();
                }

                location.reload();
            } catch (err) {
                console.error('Erreur lors de lʼenregistrement du routeur :', err);
                alert('Erreur lors de lʼenregistrement du routeur. Vérifiez la console.');
            }
        });
    }

    window.deleteRouteur = async function(id) {
        try {
            const response = await fetch(`/routeurs/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Erreur suppression');
            }
            alert('Routeur supprimé.');
            if (window.refreshNotifications) {
                window.refreshNotifications();
            }
            location.reload();
        } catch (error) {
            console.error('Erreur suppression routeur:', error);
            alert('Erreur lors de la suppression du routeur.');
        }
    };

    window.syncRouteur = async function(id) {
        try {
            const response = await fetch(`/routeurs/${id}/sync`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Erreur synchronisation');
            }

            alert(result.message || 'Synchronisation terminée');
            if (window.refreshNotifications) {
                window.refreshNotifications();
            }

            location.reload();
        } catch (error) {
            console.error('Erreur sync routeur:', error);
            alert('Erreur lors de la synchronisation du routeur.');
        }
    };

    // Si ?create=1, on ouvre directement le modal d'ajout
    const params = new URLSearchParams(window.location.search);
    if (params.get('create') === '1') {
        openModal('add');
    }

    // Fermer le modal si on clique en dehors
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    };
});

window.addEventListener('DOMContentLoaded', function() {
    const bg = document.querySelector('.dashboard-bg');
    if (bg) {
        bg.style.display = 'none';
        bg.remove();
    }

    const stats = document.querySelectorAll('.stat-value[data-value]');
    stats.forEach(el => {
        const target = parseInt(el.getAttribute('data-value') || '0', 10);
        if (isNaN(target) || target <= 0) return;

        let current = 0;
        const step = Math.max(1, Math.floor(target / 60));
        const timer = setInterval(() => {
            current = Math.min(target, current + step);
            el.textContent = current.toLocaleString('fr-FR');
            el.classList.add('updated');

            if (current >= target) {
                clearInterval(timer);
                setTimeout(() => el.classList.remove('updated'), 350);
            }
        }, 10);
    });

    // Animer les barres de performance
    window.addEventListener('load', function() {
        const perfBars = [
            { bar: '.perf-bar-cpu', value: '.perf-value-cpu', duration: 1200 },
            { bar: '.perf-bar-mem', value: '.perf-value-mem', duration: 1200 },
            { bar: '.perf-bar-temp', value: '.perf-value-temp', duration: 1200 }
        ];

        perfBars.forEach(config => {
            const bar = document.querySelector(config.bar);
            const value = document.querySelector(config.value);
            
            if (!bar || !value) return;

            const targetValue = parseFloat(value.getAttribute('data-value')) || 0;
            const maxValue = 100;
            const targetWidth = Math.min((targetValue / maxValue) * 100, 100);
            
            setTimeout(() => {
                bar.style.width = targetWidth + '%';
                
                // Animer le texte
                let current = 0;
                const step = targetValue / (config.duration / 30);
                const timer = setInterval(() => {
                    current = Math.min(current + step, targetValue);
                    const display = current >= 100 ? current.toFixed(0) : current.toFixed(1);
                    value.textContent = display.replace('.', ',') + (value.textContent.includes('°C') ? '°C' : '%');
                    
                    if (current >= targetValue) {
                        clearInterval(timer);
                        value.textContent = (targetValue >= 100 ? targetValue.toFixed(0) : targetValue.toFixed(1)).replace('.', ',') + (value.textContent.includes('°C') ? '°C' : '%');
                    }
                }, 30);
            }, 100);
        });
    });
});
</script>
@endsection