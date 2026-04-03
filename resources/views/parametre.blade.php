@extends('layouts.app')

@section('content')
<style>
    /* Styles supplémentaires pour la page Paramètres (cohérent avec login) */
    .main-content {
        position: relative;
        padding: 1.5rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-bg {
        position: absolute;
        inset: 0;
        opacity: 0.08;
        pointer-events: none;
        font-size: 8rem;
        display: flex;
        flex-wrap: wrap;
        gap: 4rem;
        justify-content: space-around;
        color: #4fc3ff;
        animation: floatBg 25s infinite ease-in-out;
    }

    @keyframes floatBg {
        0%,100% { transform: translateY(0); }
        50% { transform: translateY(-30px); }
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .header h1 {
        font-size: 2.2rem;
        background: linear-gradient(90deg, #ffffff, #81d4fa);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .btn-primary {
        background: linear-gradient(145deg, #0077e6, #00bfff);
        border: none;
        padding: 0.8rem 1.6rem;
        border-radius: 2rem;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(0, 191, 255, 0.4);
    }

    .btn-icon {
        width: 44px;
        height: 44px;
        background: rgba(10, 20, 40, 0.6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 1px solid rgba(100, 180, 255, 0.3);
        transition: all 0.25s;
    }

    .btn-icon:hover {
        background: rgba(0, 40, 60, 0.8);
        border-color: #00e5ff;
    }

    .profile-dropdown {
        position: relative;
    }

    .avatar-btn {
        width: 44px;
        height: 44px;
        background: linear-gradient(145deg, #4fc3ff, #bb86fc);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 0 15px rgba(79, 195, 255, 0.4);
    }

    .dropdown-menu {
        position: absolute;
        right: 0;
        top: 60px;
        background: rgba(10, 20, 40, 0.85);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(100, 180, 255, 0.3);
        border-radius: 1rem;
        min-width: 220px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.6);
        display: none;
        z-index: 100;
    }

    .profile-dropdown:hover .dropdown-menu {
        display: block;
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.9rem 1.2rem;
        color: #a0bcdd;
        text-decoration: none;
    }

    .dropdown-item:hover {
        background: rgba(0, 230, 255, 0.15);
        color: white;
    }

    .dropdown-divider {
        height: 1px;
        background: rgba(255,255,255,0.08);
        margin: 0.5rem 0;
    }

    .nav-item {
        background: rgba(10, 20, 40, 0.6);
        border: 1px solid rgba(100, 180, 255, 0.25);
        color: #a0bcdd;
        padding: 0.7rem 1.4rem;
        border-radius: 2rem;
        cursor: pointer;
        transition: all 0.25s;
    }

    .nav-item.active, .nav-item:hover {
        background: rgba(0, 40, 60, 0.8);
        border-color: #00e5ff;
        color: white;
    }

    .card {
        background: rgba(10, 20, 40, 0.65);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(100, 180, 255, 0.25);
        border-radius: 1.8rem;
        padding: 1.8rem;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.7);
    }

    .input-field {
        width: 100%;
        background: rgba(0, 20, 40, 0.65);
        border: 1.6px solid rgba(0, 180, 255, 0.35);
        border-radius: 1.4rem;
        padding: 0.9rem 1.4rem;
        color: white;
        font-size: 1rem;
    }

    .input-field:focus {
        border-color: #00e5ff;
        box-shadow: 0 0 0 4px rgba(0, 230, 255, 0.18);
        outline: none;
    }

    hr {
        border: none;
        border-top: 1px solid rgba(38, 63, 85, 0.6);
        margin: 1.8rem 0;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.8rem 0;
        border-bottom: 1px solid rgba(50, 100, 150, 0.3);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #7a95b8;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .info-value {
        font-weight: 600;
        color: #e0f2ff;
        text-align: right;
        flex-grow: 1;
        padding-left: 1rem;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }
</style>

<div class="main-content">
    <div class="dashboard-bg">
        <i class="fas fa-wifi"></i>
        <i class="fas fa-satellite"></i>
        <i class="fas fa-broadcast-tower"></i>
        <i class="fas fa-network-wired"></i>
        <i class="fas fa-cloud"></i>
        <i class="fas fa-server"></i>
    </div>

    @include('layouts.guest')

    <!-- Notifications de succès/erreur -->
    <div id="notification" style="display: none; padding: 1rem; margin-bottom: 1.5rem; border-radius: 1rem; border-left: 4px solid; font-weight: 600;">
        <i id="notifIcon" class="fas"></i> <span id="notifText"></span>
    </div>

    <!-- Onglets -->
    <div style="display: flex; flex-wrap: wrap; gap: 0.6rem; margin-bottom: 2.2rem; border-bottom: 1px solid #1d3347; padding-bottom: 1.2rem;">
        <button class="nav-item active" data-tab="general">Général</button>
        <button class="nav-item" data-tab="reseau">Réseau</button>
        <button class="nav-item" data-tab="securite">Sécurité</button>
        <button class="nav-item" data-tab="notifications">Notifications</button>
        <button class="nav-item" data-tab="sauvegarde">Sauvegarde</button>
        <button class="nav-item" data-tab="api">API</button>
    </div>

    <!-- Contenu des onglets -->
    <div id="general-tab" class="tab-content active">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.8rem;">
            <!-- Colonne principale - Configuration générale -->
            <div class="card">
                <h3 style="margin-bottom: 1.8rem; color: #81d4fa;">Configuration générale</h3>

                <form id="general-form">
                    <div style="margin-bottom: 1.6rem;">
                        <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Nom du système</label>
                        <input type="text" name="nom_systeme" class="input-field" value="{{ $parametres['nom_systeme'] ?? 'NetAdmin - MikroTik Controller' }}" placeholder="Nom visible dans l'interface">
                    </div>

                    <div style="margin-bottom: 1.6rem;">
                        <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Fuseau horaire</label>
                        <select name="fuseau_horaire" class="input-field">
                            <option value="Europe/Paris" {{ ($parametres['fuseau_horaire'] ?? 'Europe/Paris') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (UTC+1)</option>
                            <option value="UTC" {{ ($parametres['fuseau_horaire'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ ($parametres['fuseau_horaire'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (UTC-5/-4)</option>
                            <option value="Africa/Porto-Novo" {{ ($parametres['fuseau_horaire'] ?? '') == 'Africa/Porto-Novo' ? 'selected' : '' }}>Africa/Porto-Novo (UTC+1)</option>
                            <option value="Asia/Tokyo" {{ ($parametres['fuseau_horaire'] ?? '') == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.6rem;">
                        <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Langue de l'interface</label>
                        <select name="langue" class="input-field">
                            <option value="fr" {{ ($parametres['langue'] ?? 'fr') == 'fr' ? 'selected' : '' }}>Français</option>
                            <option value="en" {{ ($parametres['langue'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                            <option value="es" {{ ($parametres['langue'] ?? '') == 'es' ? 'selected' : '' }}>Español</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 1.6rem;">
                        <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Intervalle de rafraîchissement des stats</label>
                        <select name="intervalle_refresh" class="input-field">
                            <option value="30" {{ ($parametres['intervalle_refresh'] ?? '60') == '30' ? 'selected' : '' }}>30 secondes</option>
                            <option value="60" {{ ($parametres['intervalle_refresh'] ?? '60') == '60' ? 'selected' : '' }}>1 minute</option>
                            <option value="120" {{ ($parametres['intervalle_refresh'] ?? '') == '120' ? 'selected' : '' }}>2 minutes</option>
                            <option value="300" {{ ($parametres['intervalle_refresh'] ?? '') == '300' ? 'selected' : '' }}>5 minutes</option>
                        </select>
                    </div>

                    <div style="text-align: right; margin-top: 2rem;">
                        <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer les modifications</button>
                    </div>
                </form>
            </div>

            <!-- Colonne latérale -->
            <div class="card">
                <h3 style="margin-bottom: 1.5rem; color: #81d4fa;">Informations système</h3>

                <div class="info-item">
                    <div class="info-label">Version</div>
                    <div class="info-value">{{ $parametres['version'] ?? '2.1.5' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Dernière mise à jour</div>
                    <div class="info-value">{{ $parametres['derniere_maj'] ?? '2024-01-15 14:32' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Licence</div>
                    <div class="info-value">{{ $parametres['licence'] ?? 'Entreprise - Valide jusqu\'au 2025-12-31' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Uptime</div>
                    <div class="info-value">{{ $parametres['uptime'] ?? '12 jours 7h 42m' }}</div>
                </div>

                <hr>

                <h4 style="margin: 1.5rem 0 1rem; color: #a0bcdd;">Sauvegarde & Mise à jour</h4>
                <div style="margin-bottom: 1.2rem;">
                    <button class="btn-primary" style="width: 100%;" onclick="downloadBackup()"><i class="fas fa-download"></i> Télécharger la sauvegarde</button>
                </div>
                <div style="margin-bottom: 1.2rem;">
                    <button class="btn-primary" style="width: 100%; background: linear-gradient(145deg, #9333ea, #c084fc);" onclick="restoreBackup()"><i class="fas fa-upload"></i> Restaurer une sauvegarde</button>
                </div>
                <div style="margin-bottom: 1.2rem;">
                    <button class="btn-primary" style="width: 100%;" onclick="checkUpdates()"><i class="fas fa-sync-alt"></i> Vérifier les mises à jour</button>
                </div>
            </div>
        </div>
    </div>

    <div id="reseau-tab" class="tab-content">
        <div class="card">
            <h3 style="margin-bottom: 1.8rem; color: #81d4fa;">Configuration Réseau</h3>
            <p>Paramètres WAN, LAN, VLAN, DHCP, DNS, NAT...</p>
            <form id="reseau-form">
                <div style="margin-bottom: 1.6rem;">
                    <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Adresse IP principale</label>
                    <input type="text" name="ip_principale" class="input-field" value="{{ $parametres['ip_principale'] ?? '192.168.1.1' }}">
                </div>
                <div style="margin-bottom: 1.6rem;">
                    <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Masque de sous-réseau</label>
                    <input type="text" name="masque_reseau" class="input-field" value="{{ $parametres['masque_reseau'] ?? '255.255.255.0' }}">
                </div>
                <div style="margin-bottom: 1.6rem;">
                    <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Passerelle par défaut</label>
                    <input type="text" name="passerelle" class="input-field" value="{{ $parametres['passerelle'] ?? '192.168.1.254' }}">
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="securite-tab" class="tab-content">
        <div class="card">
            <h3 style="margin-bottom: 1.8rem; color: #81d4fa;">Configuration Sécurité</h3>
            <p>Paramètres de sécurité, firewall, authentification...</p>
            <form id="securite-form">
                <div style="margin-bottom: 1.6rem;">
                    <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Niveau de sécurité</label>
                    <select name="niveau_securite" class="input-field">
                        <option value="faible" {{ ($parametres['niveau_securite'] ?? 'moyen') == 'faible' ? 'selected' : '' }}>Faible</option>
                        <option value="moyen" {{ ($parametres['niveau_securite'] ?? 'moyen') == 'moyen' ? 'selected' : '' }}>Moyen</option>
                        <option value="eleve" {{ ($parametres['niveau_securite'] ?? '') == 'eleve' ? 'selected' : '' }}>Élevé</option>
                    </select>
                </div>
                <div style="margin-bottom: 1.6rem;">
                    <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Timeout de session (minutes)</label>
                    <input type="number" name="timeout_session" class="input-field" value="{{ $parametres['timeout_session'] ?? '30' }}">
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="notifications-tab" class="tab-content">
        <div class="card">
            <h3 style="margin-bottom: 1.8rem; color: #81d4fa;">Configuration Notifications</h3>
            <p>Paramètres d'alertes et notifications...</p>
            <form id="notifications-form">
                <div style="margin-bottom: 1.6rem;">
                    <label class="remember" style="display: block;">
                        <input type="checkbox" name="notif_email" {{ ($parametres['notif_email'] ?? true) ? 'checked' : '' }}> Notifications par email
                    </label>
                </div>
                <div style="margin-bottom: 1.6rem;">
                    <label class="remember" style="display: block;">
                        <input type="checkbox" name="notif_sms" {{ ($parametres['notif_sms'] ?? false) ? 'checked' : '' }}> Notifications par SMS
                    </label>
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="sauvegarde-tab" class="tab-content">
        <div class="card">
            <h3 style="margin-bottom: 1.8rem; color: #81d4fa;">Configuration Sauvegarde</h3>
            <p>Paramètres de sauvegarde automatique...</p>
            <form id="sauvegarde-form">
                <div style="margin-bottom: 1.6rem;">
                    <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Fréquence de sauvegarde</label>
                    <select name="frequence_sauvegarde" class="input-field">
                        <option value="quotidienne" {{ ($parametres['frequence_sauvegarde'] ?? 'hebdomadaire') == 'quotidienne' ? 'selected' : '' }}>Quotidienne</option>
                        <option value="hebdomadaire" {{ ($parametres['frequence_sauvegarde'] ?? 'hebdomadaire') == 'hebdomadaire' ? 'selected' : '' }}>Hebdomadaire</option>
                        <option value="mensuelle" {{ ($parametres['frequence_sauvegarde'] ?? '') == 'mensuelle' ? 'selected' : '' }}>Mensuelle</option>
                    </select>
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <div id="api-tab" class="tab-content">
        <div class="card">
            <h3 style="margin-bottom: 1.8rem; color: #81d4fa;">Configuration API</h3>
            <p>Paramètres d'accès API...</p>
            <form id="api-form">
                <div style="margin-bottom: 1.6rem;">
                    <label class="remember" style="display: block;">
                        <input type="checkbox" name="api_active" {{ ($parametres['api_active'] ?? true) ? 'checked' : '' }}> Activer l'API
                    </label>
                </div>
                <div style="margin-bottom: 1.6rem;">
                    <label style="display: block; margin-bottom: 0.6rem; color: #8ba9d0;">Clé API</label>
                    <input type="text" name="api_key" class="input-field" value="{{ $parametres['api_key'] ?? '' }}" readonly>
                    <button type="button" class="btn-primary" style="margin-top: 0.5rem;" onclick="regenerateApiKey()">Régénérer</button>
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showNotification(message, isSuccess = true) {
        const notif = document.getElementById('notification');
        const icon = document.getElementById('notifIcon');
        const text = document.getElementById('notifText');
        
        notif.style.display = 'block';
        notif.style.borderLeftColor = isSuccess ? '#2ef75b' : '#ff5e7c';
        notif.style.background = isSuccess ? 'rgba(46, 247, 91, 0.1)' : 'rgba(255, 94, 124, 0.1)';
        notif.style.color = isSuccess ? '#2ef75b' : '#ff5e7c';
        
        icon.className = isSuccess ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        text.textContent = message;
        
        setTimeout(() => {
            notif.style.display = 'none';
        }, 4000);
    }

    // Gestion des onglets
    document.querySelectorAll('.nav-item').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            const tabId = btn.getAttribute('data-tab') + '-tab';
            document.getElementById(tabId).classList.add('active');
        });
    });

    // Gestion des formulaires avec AJAX
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';
            
            const formData = new FormData(this);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            fetch('{{ route("parametres.updateAll") }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (result.success) {
                    showNotification('✅ Paramètres enregistrés avec succès !', true);
                } else {
                    showNotification('❌ ' + (result.message || 'Erreur lors de l\'enregistrement'), false);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showNotification('❌ Erreur de connexion', false);
            });
        });
    });

    // Boutons de sauvegarde
    function downloadBackup() {
        if (confirm('Télécharger une sauvegarde du système ?')) {
            window.location.href = '{{ route("parametres.backup.download") }}';
        }
    }

    function restoreBackup() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.zip,.tar,.sql';
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('backup', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                
                fetch('{{ route("parametres.backup.restore") }}', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showNotification('✅ Sauvegarde restaurée ! Rechargement en cours...', true);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification('❌ ' + (result.message || 'Erreur lors de la restauration'), false);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('❌ Erreur lors du chargement du fichier', false);
                });
            }
        };
        input.click();
    }

    function checkUpdates() {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vérification...';
        
        fetch('{{ route("parametres.check-updates") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(result => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            
            if (result.success) {
                if (result.updates_available) {
                    showNotification(`✅ ${result.message} (v${result.latest_version})`, true);
                } else {
                    showNotification('✅ Système à jour !', true);
                }
            } else {
                showNotification('❌ ' + (result.message || 'Erreur lors de la vérification'), false);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            btn.disabled = false;
            btn.innerHTML = originalText;
            showNotification('❌ Erreur de connexion', false);
        });
    }

    function regenerateApiKey() {
        if (confirm('Êtes-vous sûr ? Cela invalidera l\'ancienne clé API.')) {
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';
            
            fetch('{{ route("parametres.updateAll") }}', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ regenerate_api_key: true })
            })
            .then(response => response.json())
            .then(result => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                if (result.success && result.api_key) {
                    document.querySelector('input[name="api_key"]').value = result.api_key;
                    showNotification('✅ Clé API régénérée avec succès !', true);
                } else {
                    showNotification('❌ Erreur lors de la régénération', false);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showNotification('❌ Erreur de connexion', false);
            });
        }
    }
</script>