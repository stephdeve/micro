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
            <div class="stat-value">{{ $stats['en_ligne'] }}</div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> Actifs</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-exclamation-triangle" style="color: #ffaa33;"></i> Routeurs hors ligne</div>
            <div class="stat-value">{{ $stats['hors_ligne'] }}</div>
            <div class="stat-change"><i class="fas fa-exclamation-circle"></i> Attention requise</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-microchip"></i> En maintenance</div>
            <div class="stat-value">{{ $stats['maintenance'] }}</div>
            <div class="stat-change">{{ $stats['modeles'] }} modèles différents</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-version"></i> Firmware</div>
            <div class="stat-value">{{ $stats['derniere_version'] }}</div>
            <div class="stat-change">Dernière version</div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="filters-section" style="margin-bottom: 2rem;">
        <form method="GET" action="{{ route('routeurs.index') }}" id="filter-form">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
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
                    <a href="{{ route('routeurs.index') }}" class="btn-icon" style="width: auto; padding: 0 1.5rem; border-radius: 2rem;">
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
                        <td>
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
                                <button class="action-btn" style="background:#1f3145;" onclick="syncRouteur({{ $routeur->id }})" title="Synchroniser">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button class="action-btn" style="background:#1f3145;" title="Console">
                                    <i class="fas fa-terminal"></i>
                                </button>
                                <button class="action-btn delete" onclick="deleteRouteur({{ $routeur->id }})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem;">
                            <i class="fas fa-router" style="font-size: 3rem; color: #2a5f8a; margin-bottom: 1rem;"></i>
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
        <div style="margin-top: 2rem;">
            {{ $routeurs->links() }}
        </div>
        @endif
    </div>

    <!-- Carte réseau -->
    <div class="router-section" style="margin-top: 2rem;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-project-diagram"></i> Topologie réseau</h3>
                <span class="status-badge">Vue simplifiée</span>
            </div>
            <div style="height: 300px; background: #0f1a24; border-radius: 1rem; display: flex; align-items: center; justify-content: center; border: 1px solid #2a3f5a;">
                <i class="fas fa-map-marked-alt" style="font-size: 4rem; color: #2a5f8a;"></i>
                <span style="margin-left: 1rem; color: #8ba9d0;">Carte interactive (intégration future)</span>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line"></i> Performance globale</h3>
                <i class="fas fa-ellipsis-h"></i>
            </div>
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; color: #aaccff;"><span>Charge CPU moyenne</span> <span>32%</span></div>
                <div style="height: 8px; background: #1a2c3c; border-radius: 20px; margin: 0.5rem 0 1.5rem;">
                    <div style="width: 32%; height: 8px; background: linear-gradient(90deg, #00ccff, #904eff); border-radius: 20px;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; color: #aaccff;"><span>Mémoire utilisée</span> <span>45%</span></div>
                <div style="height: 8px; background: #1a2c3c; border-radius: 20px; margin: 0.5rem 0 1.5rem;">
                    <div style="width: 45%; height: 8px; background: linear-gradient(90deg, #00ccff, #2ef75b); border-radius: 20px;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; color: #aaccff;"><span>Température moyenne</span> <span>48°C</span></div>
                <div style="height: 8px; background: #1a2c3c; border-radius: 20px; margin: 0.5rem 0;">
                    <div style="width: 48%; height: 8px; background: linear-gradient(90deg, #2ef75b, #ffaa33); border-radius: 20px;"></div>
                </div>
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
});
</script>
@endsection