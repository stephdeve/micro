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
                <input type="text" class="input-field" placeholder="Rechercher un routeur (IP, nom, modèle...)" style="width: 100%;">
            </div>
            <select class="input-field" style="width: auto; min-width: 150px;">
                <option>Tous les statuts</option>
                <option>En ligne</option>
                <option>Hors ligne</option>
                <option>En maintenance</option>
            </select>
            <select class="input-field" style="width: auto; min-width: 150px;">
                <option>Tous les modèles</option>
                <option>RB951G</option>
                <option>RB750</option>
                <option>CRS326</option>
                <option>cAP ac</option>
            </select>
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
                <tbody>
                    <tr>
                        <td><i class="fas fa-check-circle" style="color: #2ef75b;"></i> Routeur Principal</td>
                        <td>RB951G-2HnD</td>
                        <td>192.168.1.1</td>
                        <td>7.12</td>
                        <td><span class="status-active">En ligne</span></td>
                        <td>45 jours</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" title="Détails"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="Configurer"><i class="fas fa-cog"></i></button>
                                <button class="action-btn" style="background:#1f3145;" title="Console"><i class="fas fa-terminal"></i></button>
                                <button class="action-btn" style="background:#1f3145;" title="Redémarrer"><i class="fas fa-sync-alt"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-check-circle" style="color: #2ef75b;"></i> Routeur Backup</td>
                        <td>RB750Gr3</td>
                        <td>192.168.1.254</td>
                        <td>7.10</td>
                        <td><span class="status-active">En ligne</span></td>
                        <td>12 jours</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" title="Détails"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="Configurer"><i class="fas fa-cog"></i></button>
                                <button class="action-btn" style="background:#1f3145;" title="Console"><i class="fas fa-terminal"></i></button>
                                <button class="action-btn" style="background:#1f3145;" title="Redémarrer"><i class="fas fa-sync-alt"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><i class="fas fa-exclamation-circle" style="color: #ffaa33;"></i> Routeur DMZ</td>
                        <td>RB4011iGS+</td>
                        <td>192.168.2.1</td>
                        <td>7.11</td>
                        <td><span class="status-inactive">Hors ligne</span></td>
                        <td>0 jour</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" title="Détails"><i class="fas fa-eye"></i></button>
                                <button class="action-btn edit" title="Configurer"><i class="fas fa-cog"></i></button>
                                <button class="action-btn" style="background:#1f3145;" title="Console"><i class="fas fa-terminal"></i></button>
                                <button class="action-btn" style="background:#ffaa33;" title="Dépanner"><i class="fas fa-tools"></i></button>
                                <button class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
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
            form.innerHTML = '@csrf @method('DELETE')';
            document.body.appendChild(form);
            form.submit();
        }
    };

    window.syncRouteur = function(id) {
        if (confirm('Lancer la synchronisation de ce routeur ?')) {
            window.location.href = `/routeurs/${id}/sync`;
        }
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