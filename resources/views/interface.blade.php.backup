@extends('layouts.app')

@section('title', 'Interfaces')

@php
    $header_buttons = '';
@endphp

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <!-- Statistiques interfaces -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-ethernet"></i> Interfaces totales</div>
            <div class="stat-value">{{ $stats['totales'] }}</div>
            <div class="stat-change">Sur {{ $stats['routeurs'] }} routeurs</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-circle" style="color: #2ef75b;"></i> Interfaces actives</div>
            <div class="stat-value">{{ $stats['actives'] }}</div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> +{{ $stats['nouvelles'] }} aujourd'hui</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-tachometer-alt"></i> Débit total</div>
            <div class="stat-value">{{ round($stats['debit_total'], 1) }} Mbps</div>
            <div class="stat-change">⬇️ {{ round($stats['debit_entrant'], 1) }} ⬆️ {{ round($stats['debit_sortant'], 1) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-exclamation-triangle"></i> Erreurs</div>
            <div class="stat-value">{{ $stats['erreurs'] }}</div>
            <div class="stat-change">Paquets perdus</div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-section" style="margin-bottom: 2rem;">
        <form method="GET" action="{{ route('interfaces.index') }}" id="filter-form">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <select name="routeur_id" class="input-field" style="width: auto; min-width: 200px;" onchange="document.getElementById('filter-form').submit()">
                    <option value="">Tous les routeurs</option>
                    @foreach($routeurs as $routeur)
                        <option value="{{ $routeur->id }}" {{ request('routeur_id') == $routeur->id ? 'selected' : '' }}>{{ $routeur->nom }}</option>
                    @endforeach
                </select>
                <select name="type" class="input-field" style="width: auto; min-width: 150px;" onchange="document.getElementById('filter-form').submit()">
                    <option value="">Tous les types</option>
                    <option value="ethernet" {{ request('type') == 'ethernet' ? 'selected' : '' }}>Ethernet</option>
                    <option value="wifi" {{ request('type') == 'wifi' ? 'selected' : '' }}>WiFi</option>
                    <option value="bridge" {{ request('type') == 'bridge' ? 'selected' : '' }}>Bridge</option>
                    <option value="vlan" {{ request('type') == 'vlan' ? 'selected' : '' }}>VLAN</option>
                </select>
                <select name="statut" class="input-field" style="width: auto; min-width: 150px;" onchange="document.getElementById('filter-form').submit()">
                    <option value="">Tous les statuts</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    <option value="erreur" {{ request('statut') == 'erreur' ? 'selected' : '' }}>En erreur</option>
                </select>
                @if(request()->anyFilled(['routeur_id', 'type', 'statut']))
                    <a href="{{ route('interfaces.index') }}" class="btn-icon" style="width: auto; padding: 0 1.5rem; border-radius: 2rem;">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Liste des interfaces -->
    <div class="table-section">
        <div class="section-header">
            <h2><i class="fas fa-list"></i> Interfaces réseau</h2>
            <div style="display: flex; gap: 1rem;">
                <button class="btn-add" onclick="openModal('add')">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
                <button class="btn-add"><i class="fas fa-download"></i> Exporter CSV</button>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Interface</th>
                        <th>Routeur</th>
                        <th>Type</th>
                        <th>Adresse MAC</th>
                        <th>Statut</th>
                        <th>Débit (Rx/Tx)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($interfaces as $interface)
                    <tr>
                        <td><i class="fas fa-{{ $interface->type == 'wifi' ? 'wifi' : 'ethernet' }}"></i> {{ $interface->nom }}</td>
                        <td>{{ $interface->routeur->nom ?? 'N/A' }}</td>
                        <td>{{ ucfirst($interface->type) }} {{ $interface->bande ? '('.$interface->bande.')' : '' }}</td>
                        <td>{{ $interface->adresse_mac ?? 'N/A' }}</td>
                        <td style="white-space: nowrap;">
                            <span class="status-{{ $interface->statut == 'actif' ? 'active' : ($interface->statut == 'erreur' ? 'inactive' : 'inactive') }}">
                                {{ ucfirst($interface->statut) }}
                            </span>
                        </td>
                        <td>{{ number_format($interface->debit_entrant ?? 0, 1) }} / {{ number_format($interface->debit_sortant ?? 0, 1) }} Mbps</td>
                        <td>
                            <div class="action-buttons">
                                <button class="action-btn view" onclick="window.location='{{ route('interfaces.show', $interface) }}'" title="Détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="action-btn edit" onclick="editInterface({{ $interface->id }})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="action-btn" style="background:#1f3145;" onclick="window.location='{{ route('interfaces.graph', $interface) }}'" title="Graphiques">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button class="action-btn" style="background:#1f3145;" onclick="toggleInterface({{ $interface->id }})" title="Activer/Désactiver">
                                    <i class="fas fa-power-off"></i>
                                </button>
                                <button class="action-btn delete" onclick="deleteInterface({{ $interface->id }})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem;">
                            <i class="fas fa-network-wired" style="font-size: 3rem; color: #2a5f8a; margin-bottom: 1rem;"></i>
                            <p>Aucune interface trouvée</p>
                            <button class="btn-primary" onclick="openModal('add')" style="margin-top: 1rem;">
                                <i class="fas fa-plus"></i> Ajouter une interface
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($interfaces->hasPages())
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
                Page <strong>{{ $interfaces->currentPage() }}</strong> / {{ $interfaces->lastPage() }}  •  <strong>{{ $interfaces->total() }}</strong> interface{{ $interfaces->total() > 1 ? 's' : '' }}
            </div>
            <div id="interfacesPaginationContainer" style="display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center;">
                @php
                    $from = max(1, $interfaces->currentPage() - 2);
                    $to = min($interfaces->lastPage(), $interfaces->currentPage() + 2);
                @endphp

                @if($from > 1)
                    <button class="pagination-btn" onclick="loadInterfacePage(1)">1</button>
                @endif

                @if($from > 2)
                    <span style="color: #6b7f96; padding: 0 0.3rem;">…</span>
                @endif

                @for($page = $from; $page <= $to; $page++)
                    @if($page === $interfaces->currentPage())
                        <span class="pagination-btn active">{{ $page }}</span>
                    @else
                        <button class="pagination-btn" onclick="loadInterfacePage({{ $page }})">{{ $page }}</button>
                    @endif
                @endfor

                @if($to < $interfaces->lastPage() - 1)
                    <span style="color: #6b7f96; padding: 0 0.3rem;">…</span>
                @endif

                @if($to < $interfaces->lastPage())
                    <button class="pagination-btn" onclick="loadInterfacePage({{ $interfaces->lastPage() }})">{{ $interfaces->lastPage() }}</button>
                @endif
            </div>
        </div>

        <script>
            function loadInterfacePage(page) {
                const params = new URLSearchParams(window.location.search);
                params.set('page', page);
                
                fetch(`{{ route('interfaces.index') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Remplacer le tbody
                    const oldTbody = document.querySelector('.table-section tbody');
                    const newTbody = doc.querySelector('.table-section tbody');
                    if (oldTbody && newTbody) {
                        oldTbody.replaceWith(newTbody);
                    }
                    
                    // Remplacer la pagination
                    const oldPagination = document.getElementById('interfacesPaginationContainer');
                    const newPagination = doc.getElementById('interfacesPaginationContainer');
                    if (oldPagination && newPagination) {
                        oldPagination.replaceWith(newPagination);
                    }
                    
                    // Scroll vers le tableau
                    document.querySelector('.table-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    // Mettre à jour l'URL
                    window.history.pushState(null, '', `{{ route('interfaces.index') }}?${params.toString()}`);
                })
                .catch(error => console.error('Erreur chargement:', error));
            }
        </script>
        @endif
    </div>
</div>


<!-- MODAL AJOUTER/MODIFIER UNE INTERFACE -->
<div id="interfaceModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: #132231; border-radius: 2rem; padding: 2rem; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; border: 1px solid #2e4b6b;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h3 id="modalTitle" style="color: white;"><i class="fas fa-plus-circle"></i> Ajouter une interface</h3>
            <button onclick="closeModal()" style="background: none; border: none; color: #8ba9d0; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>

        <form id="interfaceForm" method="POST" action="{{ route('interfaces.store') }}">
            @csrf
            <input type="hidden" id="method" name="_method" value="POST">

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Routeur *</label>
                <select name="routeur_id" id="routeur_id" class="input-field" required>
                    <option value="">Sélectionner un routeur</option>
                    @foreach($routeurs as $routeur)
                        <option value="{{ $routeur->id }}">{{ $routeur->nom }} ({{ $routeur->adresse_ip }})</option>
                    @endforeach
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Nom de l'interface *</label>
                <input type="text" name="nom" id="nom" class="input-field" placeholder="ex: ether1, wlan1, bridge-local" required>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Type *</label>
                <select name="type" id="type" class="input-field" required onchange="toggleWifiFields()">
                    <option value="ethernet">Ethernet</option>
                    <option value="wifi">WiFi</option>
                    <option value="bridge">Bridge</option>
                    <option value="vlan">VLAN</option>
                </select>
            </div>

            <div id="wifiFields" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Bande</label>
                        <select name="bande" id="bande" class="input-field">
                            <option value="2.4GHz">2.4 GHz</option>
                            <option value="5GHz">5 GHz</option>
                            <option value="6GHz">6 GHz</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">SSID</label>
                        <input type="text" name="ssid" id="ssid" class="input-field" placeholder="Nom du réseau WiFi">
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Adresse MAC</label>
                <input type="text" name="adresse_mac" id="adresse_mac" class="input-field" placeholder="00:11:22:33:44:55">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Adresse IP</label>
                <input type="text" name="adresse_ip" id="adresse_ip" class="input-field" placeholder="192.168.1.1">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Masque</label>
                <input type="text" name="mask" id="mask" class="input-field" placeholder="255.255.255.0">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">VLAN ID</label>
                <input type="number" name="vlan_id" id="vlan_id" class="input-field" placeholder="10" min="1" max="4094">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8ba9d0;">Statut *</label>
                <select name="statut" id="statut" class="input-field" required>
                    <option value="actif">Actif</option>
                    <option value="inactif">Inactif</option>
                    <option value="erreur">En erreur</option>
                </select>
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
    console.log('✅ Script interfaces chargé');
    
    const modal = document.getElementById('interfaceModal');
    const form = document.getElementById('interfaceForm');
    
    // Fonction pour afficher/masquer les champs WiFi
    window.toggleWifiFields = function() {
        const type = document.getElementById('type').value;
        const wifiFields = document.getElementById('wifiFields');
        if (type === 'wifi') {
            wifiFields.style.display = 'block';
        } else {
            wifiFields.style.display = 'none';
        }
    };

    // Intercepter la soumission du formulaire
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('📤 Soumission du formulaire');
        
        const formData = new FormData(form);
        const action = form.action;
        const method = document.getElementById('method').value;
        
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
            if (window.refreshNotifications) {
                window.refreshNotifications();
            }
            window.location.reload();
        })
        .catch(error => {
            console.error('❌ Erreur:', error);
            if (error.errors) {
                let errorMessage = 'Erreurs de validation:\n';
                for (let field in error.errors) {
                    errorMessage += `- ${field}: ${error.errors[field].join(', ')}\n`;
                }
                alert(errorMessage);
            } else {
                alert('Une erreur est survenue. Vérifiez la console (F12).');
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
            title.innerHTML = '<i class="fas fa-plus-circle"></i> Ajouter une interface';
            form.action = "{{ route('interfaces.store') }}";
            methodInput.value = 'POST';
            form.reset();
            toggleWifiFields();
        } else if (action === 'edit' && id) {
            title.innerHTML = '<i class="fas fa-edit"></i> Modifier l\'interface';
            form.action = "{{ url('interfaces') }}/" + id;
            methodInput.value = 'PUT';
            
            // Charger les données
            fetch(`/interfaces/${id}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('📊 Données reçues:', data);
                document.getElementById('routeur_id').value = data.routeur_id || '';
                document.getElementById('nom').value = data.nom || '';
                document.getElementById('type').value = data.type || 'ethernet';
                document.getElementById('adresse_mac').value = data.adresse_mac || '';
                document.getElementById('adresse_ip').value = data.adresse_ip || '';
                document.getElementById('mask').value = data.mask || '';
                document.getElementById('vlan_id').value = data.vlan_id || '';
                document.getElementById('statut').value = data.statut || 'actif';
                document.getElementById('description').value = data.description || '';
                
                if (data.type === 'wifi') {
                    document.getElementById('bande').value = data.bande || '2.4GHz';
                    document.getElementById('ssid').value = data.ssid || '';
                }
                toggleWifiFields();
            })
            .catch(error => {
                console.error('❌ Erreur chargement:', error);
                alert('Impossible de charger les données de l\'interface');
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

    window.editInterface = function(id) {
        openModal('edit', id);
    };

    window.deleteInterface = function(id) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/interfaces/${id}`;
        form.innerHTML = '@csrf @method('DELETE')';
        document.body.appendChild(form);
        form.submit();
    };

    window.toggleInterface = function(id) {
        // Logique pour activer/désactiver l'interface sans confirmation
        window.location.href = `/interfaces/${id}/toggle`;
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