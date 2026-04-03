@extends('layouts.app')

@section('content')
<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i><i class="fas fa-satellite"></i><i class="fas fa-broadcast-tower"></i><i class="fas fa-network-wired"></i>
    </div>

    @include('layouts.guest')

    <!-- Statistiques sécurité -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-shield-virus"></i> Niveau de sécurité</div>
            <div class="stat-value" style="color: #2ef79b;">{{ $stats['niveau_securite'] ?? 90 }}%</div>
            <div class="stat-change">{{ $stats['niveau_securite'] >= 80 ? 'Excellent' : ($stats['niveau_securite'] >= 50 ? 'Moyen' : 'Faible') }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-skull-crosswalk"></i> Tentatives bloquées</div>
            <div class="stat-value">{{ $stats['tentatives_bloc'] ?? 0 }}</div>
            <div class="stat-change">+{{ $stats['tentatives_bloc_today'] ?? 0 }} aujourd'hui</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-firewall"></i> Règles firewall</div>
            <div class="stat-value">{{ $stats['regles_firewall'] ?? 0 }}</div>
            <div class="stat-change">Actives</div>
        </div>
        <div class="stat-card">
            <div class="stat-title"><i class="fas fa-lock"></i> Connexions TLS</div>
            <div class="stat-value">{{ $stats['connexions_tls'] ?? 0 }}</div>
            <div class="stat-change">Chiffrées</div>
        </div>
    </div>

    <!-- Alertes et incidents -->
    <div class="router-section">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle" style="color: #ffaa33;"></i> Alertes récentes</h3>
                <span class="status-badge" id="badge-alertes">{{ $stats['alertes_non_resolues'] ?? 0 }} non résolues</span>
            </div>
            <div id="alertes-list" style="display: flex; flex-direction: column; gap: 1rem;">
                @forelse($alertes as $alerte)
                <div class="alerte-item" style="background: #1f2128; padding: 1rem; border-radius: 1rem; border-left: 4px solid {{ $alerte->severite == 'critique' ? '#ff5e7c' : ($alerte->severite == 'haute' ? '#ffaa33' : '#2ef75b') }};">
                    <div style="display: flex; justify-content: space-between; gap: 0.5rem; align-items: center;">
                        <div>
                            <span><i class="fas fa-{{ $alerte->severite == 'critique' ? 'skull-crosswalk' : ($alerte->type == 'intrusion' ? 'exclamation-triangle' : 'check-circle') }}" style="color: {{ $alerte->severite == 'critique' ? '#ff5e7c' : ($alerte->severite == 'haute' ? '#ffaa33' : '#2ef75b') }};"></i> <strong>{{ $alerte->nom_evenement }}</strong></span>
                            <span style="color: #8ba9d0; margin-left: 0.75rem;">{{ $alerte->created_at->diffForHumans() }}</span>
                        </div>
                        <div style="display: flex; gap: 0.5rem;">
                            @if($alerte->statut != 'resolu')
                                <button class="action-btn mark-resolved" data-id="{{ $alerte->id }}" title="Marquer résolu"><i class="fas fa-check-circle" style="color: #2ef75b;"></i></button>
                            @endif
                            <button class="action-btn archive-alert" data-id="{{ $alerte->id }}" title="Archiver"><i class="fas fa-archive" style="color: #ffaa33;"></i></button>
                            <button class="action-btn delete-alert" data-id="{{ $alerte->id }}" title="Supprimer"><i class="fas fa-trash" style="color: #ff5e7c;"></i></button>
                        </div>
                    </div>
                    <div style="margin-top: 0.5rem;">{{ Str::limit($alerte->description, 100) }}</div>
                    <div style="margin-top:0.5rem; font-size:.8rem; color:#8ba9d0;">Statut : {{ ucfirst($alerte->statut) }} {{ $alerte->resolu_a ? '• résolu ' . $alerte->resolu_a->diffForHumans() : '' }}</div>
                </div>
                @empty
                <div style="background: #1f2128; padding: 1rem; border-radius: 1rem; border-left: 4px solid #2ef75b;">
                    <div>Aucune alerte récente.</div>
                </div>
                @endforelse
            </div>

            <div id="alertes-pagination-wrapper">
                @if($alertes->hasPages())
                    <div id="alertes-pagination" class="pagination-wrapper custom-pagination" style="margin-top: 1rem; display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="{{ $alertes->url(1) }}" class="page-item {{ $alertes->onFirstPage() ? 'disabled' : '' }}">« Début</a>
                        <a href="{{ $alertes->previousPageUrl() ?: '#' }}" class="page-item {{ $alertes->onFirstPage() ? 'disabled' : '' }}">‹</a>

                        @foreach(range(max(1, $alertes->currentPage() - 2), min($alertes->lastPage(), $alertes->currentPage() + 2)) as $page)
                            <a href="{{ $alertes->url($page) }}" class="page-item {{ $alertes->currentPage() == $page ? 'active' : '' }}">{{ $page }}</a>
                        @endforeach

                        <a href="{{ $alertes->nextPageUrl() ?: '#' }}" class="page-item {{ $alertes->currentPage() == $alertes->lastPage() ? 'disabled' : '' }}">›</a>
                        <a href="{{ $alertes->url($alertes->lastPage()) }}" class="page-item {{ $alertes->currentPage() == $alertes->lastPage() ? 'disabled' : '' }}">Fin »</a>
                    </div>

                    <div id="alertes-pagination-info" style="text-align:center; margin-top:0.7rem; color:#8ba9d0; font-size:0.9rem;">
                        Page {{ $alertes->currentPage() }} / {{ $alertes->lastPage() }} — {{ $alertes->total() }} alertes
                    </div>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; gap:0.75rem;">
                <h3><i class="fas fa-firewall"></i> Règles firewall actives</h3>
                <button id="add-firewall-rule" class="btn-add" style="padding: 0.3rem 1rem;"><i class="fas fa-plus"></i> Ajouter</button>
            </div>
            <div id="add-firewall-rule-form" style="display:none; background:#111d2f; margin-top:0.8rem; padding:0.75rem; border-radius:0.75rem; border:1px solid #22334a;">
                <div style="display:flex; flex-wrap:wrap; gap:0.6rem; align-items:center;">
                    <input id="fw-rule-nom" type="text" placeholder="Nom de la règle" style="flex:1; min-width:180px; padding:0.4rem 0.55rem; background:#0f1b2f; border:1px solid #1f3347; color:#ffffff; border-radius:0.4rem;" />
                    <select id="fw-rule-chain" style="padding:0.4rem 0.55rem; background:#0f1b2f; border:1px solid #1f3347; color:#ffffff; border-radius:0.4rem;">
                        <option value="input">input</option>
                        <option value="output">output</option>
                        <option value="forward">forward</option>
                        <option value="prerouting">prerouting</option>
                        <option value="postrouting">postrouting</option>
                    </select>
                    <select id="fw-rule-action" style="padding:0.4rem 0.55rem; background:#0f1b2f; border:1px solid #1f3347; color:#ffffff; border-radius:0.4rem;">
                        <option value="accept">accept</option>
                        <option value="drop">drop</option>
                        <option value="reject">reject</option>
                        <option value="jump">jump</option>
                        <option value="log">log</option>
                    </select>
                    <button id="save-firewall-rule" class="btn-add" style="padding:0.4rem 0.8rem;">Enregistrer</button>
                    <button id="cancel-firewall-rule" class="btn-add" style="padding:0.4rem 0.8rem; background:#4d5d74;">Annuler</button>
                </div>
            </div>
            <div style="max-height: 300px; overflow-y: auto;" id="rule-list">
                @forelse($stats['regles_firewall_list'] ?? collect() as $rule)
                    <div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;">
                        <div><i class="fas fa-check-circle" style="color: #2ef75b;"></i> <strong>{{ ucfirst($rule->chain ?: 'Règle') }}:</strong> {{ $rule->nom ?? 'Sans nom' }}</div>
                    </div>
                @empty
                    <div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;">
                        <div>Aucune règle active.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sessions actives -->
    <div class="table-section">
        <div class="section-header">
            <h2><i class="fas fa-users"></i> Sessions actives</h2>
            <button id="refresh-securite" class="btn-add"><i class="fas fa-sync-alt"></i> Actualiser</button>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Adresse IP</th>
                        <th>Type</th>
                        <th>Début</th>
                        <th>Durée</th>
                        <th>Trafic</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stats['sessions_actives'] ?? collect() as $session)
                        @php
                            $startTime = $session->last_activity ? \Carbon\Carbon::createFromTimestamp($session->last_activity) : null;
                            $dur = $startTime ? $startTime->diffForHumans(['parts' => 2, 'short' => true]) : '-';
                        @endphp
                        <tr>
                            <td><i class="fas fa-user"></i> {{ $session->user_name ?? ($session->user_email ?? 'Invité') }}</td>
                            <td>{{ $session->ip_address ?? 'N/A' }}</td>
                            <td>{{ $session->user_agent ? (\Illuminate\Support\Str::contains($session->user_agent, 'MikroTik') ? 'MikroTik' : 'Web') : 'Inconnu' }}</td>
                            <td>{{ $startTime ? $startTime->format('H:i:s') : '-' }}</td>
                            <td>{{ $dur }}</td>
                            <td>-</td>
                            <td><button class="action-btn" title="Déconnecter"><i class="fas fa-ban" style="color: #ff5e7c;"></i></button></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center;">Aucune session active détectée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    async function refreshSecuriteData(page = 1) {
        try {
            console.log('🔄 Refresh données securité page ' + page);
            const res = await fetch('/securite/data?page=' + page, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!res.ok) {
                console.error('❌ Impossible de charger les données securite', res.status);
                alert('Erreur lors du chargement. Code: ' + res.status);
                return;
            }
            const stats = await res.json();
            console.log('✅ Données reçues:', stats);

            // Mise à jour stats cartes
            const badgeAlertes = document.querySelector('#badge-alertes');
            if (badgeAlertes) badgeAlertes.textContent = `${stats.alertes_non_resolues ?? 0} non résolues`;

            const statCards = document.querySelectorAll('.stats-grid .stat-card');
            if (statCards[0]) statCards[0].querySelector('.stat-value').textContent = `${stats.niveau_securite ?? 0}%`;
            if (statCards[1]) {
                statCards[1].querySelector('.stat-value').textContent = `${stats.tentatives_bloc ?? 0}`;
                statCards[1].querySelector('.stat-change').textContent = `+${stats.tentatives_bloc_today ?? 0} aujourd'hui`;
            }
            if (statCards[2]) statCards[2].querySelector('.stat-value').textContent = `${stats.regles_firewall ?? 0}`;
            if (statCards[3]) statCards[3].querySelector('.stat-value').textContent = `${stats.connexions_tls ?? 0}`;

            // Mise à jour règles firewall
            const rulesContainer = document.getElementById('rule-list');
            if (rulesContainer) {
                rulesContainer.innerHTML = '';
                if ((stats.regles_firewall_list || []).length === 0) {
                    rulesContainer.innerHTML = '<div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;"><div>Aucune règle active.</div></div>';
                } else {
                    stats.regles_firewall_list.forEach(rule => {
                        rulesContainer.innerHTML += `<div style="padding: 0.5rem 0; border-bottom: 1px solid #1d3347;"><div><i class="fas fa-check-circle" style="color: #2ef75b;"></i> <strong>${(rule.chain || 'Règle').charAt(0).toUpperCase() + (rule.chain || 'Règle').slice(1)}:</strong> ${rule.nom || 'Sans nom'}</div></div>`;
                    });
                }
            }

            // Mise à jour alertes
            const alerteList = document.getElementById('alertes-list');
            if (alerteList) {
                alerteList.innerHTML = '';
                if ((stats.alertes || []).length === 0) {
                    alerteList.innerHTML = '<div style="background: #1f2128; padding: 1rem; border-radius: 1rem; border-left: 4px solid #2ef75b;"><div>Aucune alerte récente.</div></div>';
                } else {
                    stats.alertes.forEach(alerte => {
                        const couleur = alerte.severite === 'critique' ? '#ff5e7c' : alerte.severite === 'haute' ? '#ffaa33' : '#2ef75b';
                        const icon = alerte.severite === 'critique' ? 'skull-crosswalk' : (alerte.type === 'intrusion' ? 'exclamation-triangle' : 'check-circle');
                        const age = new Date(alerte.created_at).toLocaleString('fr-FR');

                        let actions = '';
                        if (alerte.statut !== 'resolu') {
                            actions += `<button class="action-btn mark-resolved" data-id="${alerte.id}" title="Marquer résolu"><i class="fas fa-check-circle" style="color: #2ef75b;"></i></button>`;
                        }
                        actions += `<button class="action-btn archive-alert" data-id="${alerte.id}" title="Archiver"><i class="fas fa-archive" style="color: #ffaa33;"></i></button>`;
                        actions += `<button class="action-btn delete-alert" data-id="${alerte.id}" title="Supprimer"><i class="fas fa-trash" style="color: #ff5e7c;"></i></button>`;

                        alerteList.innerHTML += `
                            <div class="alerte-item" style="background: #1f2128; padding: 1rem; border-radius: 1rem; border-left: 4px solid ${couleur};">
                                <div style="display: flex; justify-content: space-between; gap: 0.5rem; align-items: center;">
                                    <div>
                                        <span><i class="fas fa-${icon}" style="color: ${couleur};"></i> <strong>${alerte.nom_evenement}</strong></span>
                                        <span style="color: #8ba9d0; margin-left: 0.75rem;">${age}</span>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem;">${actions}</div>
                                </div>
                                <div style="margin-top: 0.5rem;">${alerte.description ? alerte.description.substring(0, 100) : ''}</div>
                                <div style="margin-top:0.5rem; font-size:.8rem; color:#8ba9d0;">Statut : ${alerte.statut ? alerte.statut.charAt(0).toUpperCase() + alerte.statut.slice(1) : 'Inconnu'}</div>
                            </div>
                        `;
                    });
                }
            }

            // Mise à jour pagination alertes
            const paginationContainer = document.getElementById('alertes-pagination');
            const paginationInfo = document.getElementById('alertes-pagination-info');
            if (paginationContainer && paginationInfo) {
                if (!stats.alertes_last_page || stats.alertes_last_page <= 1) {
                    paginationContainer.innerHTML = '';
                    paginationInfo.textContent = `Page 1 / 1 — ${stats.alertes_total || 0} alertes`;
                } else {
                    const current = stats.alertes_current_page || 1;
                    const last = stats.alertes_last_page;
                    paginationContainer.innerHTML = '';

                    const button = (label, page, disabled) => `<a href="#" class="page-item ${disabled ? 'disabled' : ''}" data-page="${page}">${label}</a>`;

                    paginationContainer.innerHTML += button('« Début', 1, current === 1);
                    paginationContainer.innerHTML += button('‹', Math.max(1, current - 1), current === 1);

                    for (let p = Math.max(1, current - 2); p <= Math.min(last, current + 2); p++) {
                        paginationContainer.innerHTML += button(p, p, false);
                    }

                    paginationContainer.innerHTML += button('›', Math.min(last, current + 1), current === last);
                    paginationContainer.innerHTML += button('Fin »', last, current === last);

                    paginationInfo.textContent = `Page ${current} / ${last} — ${stats.alertes_total || 0} alertes`;

                    paginationContainer.querySelectorAll('.page-item').forEach(el => {
                        const page = parseInt(el.dataset.page, 10);
                        if (!isNaN(page) && !el.classList.contains('disabled')) {
                            el.addEventListener('click', e => {
                                e.preventDefault();
                                refreshSecuriteData(page);
                            });
                        }
                    });
                }
            }

            const tbody = document.querySelector('.table-section table tbody');
            if (tbody) {
                tbody.innerHTML = '';
                if ((stats.sessions_actives || []).length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" style="text-align: center;">Aucune session active détectée.</td></tr>`;
                } else {
                    stats.sessions_actives.forEach(session => {
                        const startTime = session.last_activity ? new Date(session.last_activity * 1000) : null;
                        const horaire = startTime ? startTime.toLocaleTimeString('fr-FR') : '-';
                        const duree = startTime ? Math.max(0, Math.floor((Date.now() - startTime.getTime()) / 60000)) + ' min' : '-';
                        const agent = session.user_agent && session.user_agent.includes('MikroTik') ? 'MikroTik' : 'Web';
                        const user = session.user_name || session.user_email || 'Invité';

                        tbody.innerHTML += `
                            <tr data-session-id="${session.id}">
                                <td><i class="fas fa-user"></i> ${user}</td>
                                <td>${session.ip_address || 'N/A'}</td>
                                <td>${agent}</td>
                                <td>${horaire}</td>
                                <td>${duree}</td>
                                <td>-</td>
                                <td><button class="action-btn disconnect-session" data-session-id="${session.id}" title="Déconnecter"><i class="fas fa-ban" style="color: #ff5e7c;"></i></button></td>
                            </tr>
                        `;
                    });
                }
            }

            document.querySelectorAll('.disconnect-session').forEach(button => {
                button.addEventListener('click', async () => {
                    const sessionId = button.dataset.sessionId;
                    await fetch(`/securite/sessions/${sessionId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    await refreshSecuriteData();
                });
            });

            document.querySelectorAll('.mark-resolved').forEach(button => {
                button.addEventListener('click', async () => {
                    const id = button.dataset.id;
                    await fetch(`/securite/alertes/${id}/resolve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    await refreshSecuriteData();
                });
            });

            document.querySelectorAll('.archive-alert').forEach(button => {
                button.addEventListener('click', async () => {
                    const id = button.dataset.id;
                    await fetch(`/securite/alertes/${id}/archive`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    await refreshSecuriteData();
                });
            });

            document.querySelectorAll('.delete-alert').forEach(button => {
                button.addEventListener('click', async () => {
                    const id = button.dataset.id;
                    await fetch(`/securite/alertes/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    await refreshSecuriteData();
                });
            });

            console.log('✅ Refresh terminé avec succès');

        } catch (error) {
            console.error('❌ Erreur lors du refresh:', error);
            alert('Erreur lors du chargement. Vérifiez la console.');
        }
    }

    function setupAddRuleButton() {
        const addBtn = document.getElementById('add-firewall-rule');
        const form = document.getElementById('add-firewall-rule-form');
        const saveBtn = document.getElementById('save-firewall-rule');
        const cancelBtn = document.getElementById('cancel-firewall-rule');

        if (!addBtn || !form || !saveBtn || !cancelBtn) return;

        addBtn.addEventListener('click', () => {
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        cancelBtn.addEventListener('click', () => {
            form.style.display = 'none';
        });

        saveBtn.addEventListener('click', async () => {
            const nom = document.getElementById('fw-rule-nom').value.trim();
            const chain = document.getElementById('fw-rule-chain').value;
            const action = document.getElementById('fw-rule-action').value;

            if (!nom) {
                alert('Veuillez saisir un nom de règle.');
                return;
            }

            const res = await fetch('{{ route('securite.firewall-rules.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nom, chain, action })
            });

            if (res.ok) {
                document.getElementById('fw-rule-nom').value = '';
                form.style.display = 'none';
                await refreshSecuriteData();
            } else {
                const json = await res.json();
                alert(json.message || 'Impossible d’ajouter la règle.');
            }
        });
    }

    function setupActionButtons() {
        document.querySelectorAll('.disconnect-session').forEach(button => {
            button.addEventListener('click', async () => {
                const sessionId = button.dataset.sessionId;
                await fetch(`/securite/sessions/${sessionId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                await refreshSecuriteData();
            });
        });

        document.querySelectorAll('.mark-resolved').forEach(button => {
            button.addEventListener('click', async () => {
                const id = button.dataset.id;
                await fetch(`/securite/alertes/${id}/resolve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                await refreshSecuriteData();
            });
        });

        document.querySelectorAll('.archive-alert').forEach(button => {
            button.addEventListener('click', async () => {
                const id = button.dataset.id;
                await fetch(`/securite/alertes/${id}/archive`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                await refreshSecuriteData();
            });
        });

        document.querySelectorAll('.delete-alert').forEach(button => {
            button.addEventListener('click', async () => {
                const id = button.dataset.id;
                await fetch(`/securite/alertes/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                await refreshSecuriteData();
            });
        });
    }

    document.getElementById('refresh-securite').addEventListener('click', function (e) {
        e.preventDefault();
        console.log('🔘 Clic sur Actualiser');
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';
        refreshSecuriteData().finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-sync-alt"></i> Actualiser';
        });
    });

    setupAddRuleButton();
    refreshSecuriteData();
</script>

@endsection