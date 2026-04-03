@extends('layouts.app')

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
            <div class="stat-value">8</div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> +2 aujourd'hui</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-exclamation-triangle" style="color: #ffaa33;"></i> Routeurs hors ligne</div>
            <div class="stat-value">2</div>
            <div class="stat-change"><i class="fas fa-exclamation-circle"></i> Attention requise</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-microchip"></i> Modèles différents</div>
            <div class="stat-value">6</div>
            <div class="stat-change">RB951, RB750, CRS, etc.</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-version"></i> Firmware</div>
            <div class="stat-value">7.12</div>
            <div class="stat-change">Dernière version</div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="filters-section" style="margin-bottom: 2rem;">
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <input id="searchInput" type="text" class="input-field" placeholder="Rechercher un routeur (IP, nom, modèle...)" style="width: 100%;">
            </div>
            <select id="statutFilter" class="input-field" style="width: auto; min-width: 150px;">
                <option value="">Tous les statuts</option>
                <option value="en_ligne">En ligne</option>
                <option value="hors_ligne">Hors ligne</option>
                <option value="maintenance">En maintenance</option>
            </select>
            <select id="modeleFilter" class="input-field" style="width: auto; min-width: 150px;">
                <option value="">Tous les modèles</option>
                @foreach($modeles as $modele)
                    <option value="{{ $modele }}">{{ $modele }}</option>
                @endforeach
            </select>
            <button id="refreshRouteurs" class="btn-add" style="height: 38px;">↻ Actualiser</button>
        </div>
    </div>

    <!-- Liste des routeurs -->
    <div class="table-section">
        <div class="section-header">
            <h2><i class="fas fa-list"></i> Routeurs disponibles</h2>
            <div style="display: flex; gap: 1rem;">
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
                <tbody id="routeursTableBody">
                    <!-- Chargement dynamique via JavaScript -->
                </tbody>
            </table>
        </div>

        <div id="routeursPagination" class="pagination" style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;"></div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Script routeurs chargé');
    
    const modal = document.getElementById('routeurModal');
    const form = document.getElementById('routeurForm');
    const tableBody = document.getElementById('routeursTableBody');
    const paginationContainer = document.getElementById('routeursPagination');
    const searchInput = document.getElementById('searchInput');
    const statutFilter = document.getElementById('statutFilter');
    const modeleFilter = document.getElementById('modeleFilter');
    const refreshRouteurs = document.getElementById('refreshRouteurs');

    function formatStatusLabel(statut) {
        if (statut === 'en_ligne') return '<span class="status-active">En ligne</span>';
        if (statut === 'hors_ligne') return '<span class="status-inactive">Hors ligne</span>';
        if (statut === 'maintenance') return '<span class="status-warning">Maintenance</span>';
        return '<span>' + statut + '</span>';
    }

    function renderRouteurs(routeurs) {
        tableBody.innerHTML = '';

        if (!routeurs || routeurs.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Aucun routeur trouvé.</td></tr>';
            return;
        }

        routeurs.forEach(routeur => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${routeur.nom || '—'}</td>
                <td>${routeur.modele || '—'}</td>
                <td>${routeur.adresse_ip || '—'}</td>
                <td>${routeur.version_ros || '—'}</td>
                <td>${formatStatusLabel(routeur.statut)}</td>
                <td>${routeur.uptime ? routeur.uptime + ' j' : 'N/A'}</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn view" title="Détails" onclick="viewRouteur(${routeur.id})"><i class="fas fa-eye"></i></button>
                        <button class="action-btn edit" title="Configurer" onclick="openModal('edit', ${routeur.id})"><i class="fas fa-cog"></i></button>
                        <button class="action-btn delete" title="Supprimer" onclick="deleteRouteur(${routeur.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            `;
            tableBody.appendChild(row);
        });
    }

    function renderPagination(pagination) {
        paginationContainer.innerHTML = '';

        if (!pagination || pagination.last_page <= 1) {
            return;
        }

        const summary = document.createElement('div');
        summary.style.marginBottom = '0.5rem';
        summary.style.color = '#b0c6e9';
        summary.style.fontSize = '0.88rem';
        summary.textContent = `Page ${pagination.current_page} / ${pagination.last_page} (${pagination.total} routeur${pagination.total > 1 ? 's' : ''})`;
        paginationContainer.appendChild(summary);

        const createBullet = (label, page, active = false, disabled = false) => {
            const bullet = document.createElement('button');
            bullet.textContent = label;
            bullet.style.cursor = disabled ? 'not-allowed' : 'pointer';
            bullet.disabled = disabled;
            bullet.style.width = '32px';
            bullet.style.height = '32px';
            bullet.style.borderRadius = '16px';
            bullet.style.border = '1px solid #2d405a';
            bullet.style.background = active ? '#1b2f42' : '#112033';
            bullet.style.color = active ? '#ffffff' : '#8ba1bd';
            bullet.style.margin = '0 0.2rem';
            bullet.style.fontSize = '0.9rem';
            if (active) {
                bullet.style.fontWeight = '700';
                bullet.style.boxShadow = '0 0 8px rgba(60, 120, 195, 0.3)';
            }
            bullet.addEventListener('click', () => {
                if (!disabled) loadRouteurs(page);
            });
            return bullet;
        };

        const range = 2;
        let start = Math.max(1, pagination.current_page - range);
        let end = Math.min(pagination.last_page, pagination.current_page + range);

        if (start > 1) {
            paginationContainer.appendChild(createBullet('1', 1));
            if (start > 2) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.style.color = '#a6b8cf';
                dots.style.padding = '0 0.5rem';
                paginationContainer.appendChild(dots);
            }
        }

        for (let i = start; i <= end; i++) {
            paginationContainer.appendChild(createBullet(String(i), i, pagination.current_page === i));
        }

        if (end < pagination.last_page) {
            if (end < pagination.last_page - 1) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.style.color = '#a6b8cf';
                dots.style.padding = '0 0.5rem';
                paginationContainer.appendChild(dots);
            }
            paginationContainer.appendChild(createBullet(String(pagination.last_page), pagination.last_page));
        }
    }

    function loadRouteurs(page = 1) {
        const params = new URLSearchParams();
        if (searchInput.value.trim()) params.set('search', searchInput.value.trim());
        if (statutFilter.value) params.set('statut', statutFilter.value);
        if (modeleFilter.value) params.set('modele', modeleFilter.value);
        params.set('page', page);

        fetch(`/routeurs/data?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            renderRouteurs(data.routeurs);
            renderPagination(data.pagination);
            console.log('Routeurs chargés', data);
        })
        .catch(error => {
            console.error('Erreur chargement routeurs:', error);
            tableBody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#f55">Erreur de chargement</td></tr>';
        });
    }

    searchInput.addEventListener('input', () => loadRouteurs(1));
    statutFilter.addEventListener('change', () => loadRouteurs(1));
    modeleFilter.addEventListener('change', () => loadRouteurs(1));
    refreshRouteurs.addEventListener('click', () => loadRouteurs(1));

    loadRouteurs();

    // Intercepter la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('📤 Soumission du formulaire');
        
        const formData = new FormData(form);
        const action = form.action;
        const method = document.getElementById('method').value;
        
        console.log('URL:', action);
        console.log('Méthode:', method);
        
        // Afficher les données du formulaire
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Envoyer la requête AJAX
        fetch(action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            console.log('📥 Réponse reçue:', response.status);
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Succès:', data);
            closeModal();
            // Recharger la page pour voir les nouvelles données
            window.location.reload();
        })
        .catch(error => {
            console.error('❌ Erreur:', error);
            if (error.errors) {
                // Afficher les erreurs de validation
                let errorMessage = 'Erreurs de validation:\n';
                for (let field in error.errors) {
                    errorMessage += `- ${field}: ${error.errors[field].join(', ')}\n`;
                }
                alert(errorMessage);
            } else {
                alert('Une erreur est survenue. Vérifiez la console (F12) pour plus de détails.');
            }
        });
    });

    window.openModal = function(action, id = null) {
        console.log('🖱️ openModal appelé', action, id);
        
        if (!modal) {
            console.error('❌ Modal non trouvé!');
            return;
        }

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
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }
                return response.json();
            })
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
                console.error('❌ Erreur chargement:', error);
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

    window.deleteRouteur = function(id) {
        if (confirm('Êtes-vous sûr de vouloir supprimer ce routeur ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/routeurs/${id}`;
            form.innerHTML = '{!! csrf_field() !!}<input type="hidden" name="_method" value="DELETE">';
            document.body.appendChild(form);
            form.submit();
        }
    };

    window.viewRouteur = function(id) {
        window.location.href = `/routeurs/${id}`;
    };

    // Fermer le modal si on clique en dehors
    window.onclick = function(event) {
        if (event.target == modal) {
            closeModal();
        }
    };
});
</script>
@endsection