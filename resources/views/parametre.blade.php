@extends('layouts.app')

@section('content')
<style>
    /* Styles supplémentaires pour la page Paramètres */
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

    .btn-primary {
        background: linear-gradient(145deg, #0077e6, #00bfff);
        border: none;
        padding: 0.8rem 1.6rem;
        border-radius: 2rem;
        color: white;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(0, 191, 255, 0.4);
    }

    .card {
        background: rgba(10, 20, 40, 0.75);
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

    .page-intro {
        margin-bottom: 2rem;
        padding: 2rem;
        border-radius: 2rem;
        background: linear-gradient(135deg, rgba(5, 30, 60, 0.95), rgba(6, 41, 75, 0.95));
        border: 1px solid rgba(80, 160, 255, 0.18);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.35);
        position: relative;
        overflow: hidden;
    }

    .page-intro::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top left, rgba(79, 195, 255, 0.22), transparent 26%), radial-gradient(circle at bottom right, rgba(0, 191, 255, 0.16), transparent 22%);
        pointer-events: none;
    }

    .page-intro > * {
        position: relative;
        z-index: 1;
    }

    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.65rem 1rem;
        border-radius: 999px;
        background: rgba(0, 89, 182, 0.22);
        color: #cbe6ff;
        font-size: 0.85rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 1rem;
        border: 1px solid rgba(79, 195, 255, 0.25);
    }

    .page-intro h1 {
        font-size: 2.8rem;
        margin-bottom: 0.75rem;
        line-height: 1.05;
        color: white;
    }

    .page-intro p {
        color: #c5d8f4;
        max-width: 720px;
        margin-bottom: 0;
    }

    .intro-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .hero-btn {
        min-width: 240px;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: 1.8fr 1fr;
        gap: 1.8rem;
    }

    .field-group {
        margin-bottom: 1.6rem;
    }

    .field-group label {
        display: block;
        margin-bottom: 0.6rem;
        color: #9bb7d8;
        font-weight: 500;
    }

    .form-actions {
        text-align: right;
    }

    .info-header {
        margin-bottom: 1.8rem;
    }

    .info-header h3 {
        margin-bottom: 0.6rem;
        color: #81d4fa;
    }

    .info-header span {
        color: #9bb7d8;
        font-size: 0.95rem;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.9rem 0;
        border-bottom: 1px solid rgba(50, 100, 150, 0.3);
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        color: #7a95b8;
        font-size: 0.95rem;
        font-weight: 500;
    }

    .info-value {
        font-weight: 600;
        color: #e0f2ff;
        text-align: right;
        padding-left: 1rem;
    }

    @media (max-width: 992px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }

        .page-intro {
            padding: 1.6rem;
        }

        .page-intro h1 {
            font-size: 2.1rem;
        }
    }

    @media (max-width: 640px) {
        .page-intro, .card {
            padding: 1.4rem;
        }

        .intro-content {
            flex-direction: column;
            align-items: stretch;
        }

        .hero-btn {
            width: 100%;
        }
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

    <div class="page-intro">
        <span class="section-label"><i class="fas fa-cog"></i> PARAMÈTRES</span>
        <div class="intro-content">
            <div>
                <h1>Réglages du système</h1>
                <p>Interface claire pour ajuster les réglages principaux du service et accéder rapidement à la sauvegarde.</p>
            </div>
            <button class="btn-primary hero-btn" onclick="downloadBackup()"><i class="fas fa-download"></i> Télécharger une sauvegarde</button>
        </div>
    </div>

    <div class="settings-grid">
        <div class="card">
            <h3 style="margin-bottom: 0.8rem; color: #81d4fa;">Réglages généraux</h3>
            <p style="margin-bottom: 1.8rem; color: #9bb7d8;">Configurez les paramètres principaux de votre application.</p>

            <form id="general-form">
                <div class="field-group">
                    <label>Nom du service</label>
                    <input type="text" name="nom_systeme" class="input-field" value="{{ $parametres['nom_systeme'] ?? 'NetAdmin' }}" placeholder="Nom du service">
                </div>

                <div class="field-group">
                    <label>Langue par défaut</label>
                    <select name="langue" class="input-field">
                        <option value="fr" {{ ($parametres['langue'] ?? 'fr') == 'fr' ? 'selected' : '' }}>Français</option>
                        <option value="en" {{ ($parametres['langue'] ?? '') == 'en' ? 'selected' : '' }}>English</option>
                        <option value="es" {{ ($parametres['langue'] ?? '') == 'es' ? 'selected' : '' }}>Español</option>
                    </select>
                </div>

                <div class="field-group">
                    <label>Mode maintenance</label>
                    <select name="maintenance_mode" class="input-field">
                        <option value="off" {{ ($parametres['maintenance_mode'] ?? 'off') == 'off' ? 'selected' : '' }}>Désactivé</option>
                        <option value="on" {{ ($parametres['maintenance_mode'] ?? '') == 'on' ? 'selected' : '' }}>Activé</option>
                    </select>
                </div>

                <div class="field-group">
                    <label>Fuseau horaire</label>
                    <select name="fuseau_horaire" class="input-field">
                        <option value="Europe/Paris" {{ ($parametres['fuseau_horaire'] ?? 'Europe/Paris') == 'Europe/Paris' ? 'selected' : '' }}>Europe/Paris (UTC+1)</option>
                        <option value="UTC" {{ ($parametres['fuseau_horaire'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                        <option value="America/New_York" {{ ($parametres['fuseau_horaire'] ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (UTC-5/-4)</option>
                        <option value="Africa/Porto-Novo" {{ ($parametres['fuseau_horaire'] ?? '') == 'Africa/Porto-Novo' ? 'selected' : '' }}>Africa/Porto-Novo (UTC+1)</option>
                        <option value="Asia/Tokyo" {{ ($parametres['fuseau_horaire'] ?? '') == 'Asia/Tokyo' ? 'selected' : '' }}>Asia/Tokyo</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Enregistrer les paramètres</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="info-header">
                <h3>Informations</h3>
                <span>État actuel du système et informations utiles.</span>
            </div>

            <div class="info-item">
                <div class="info-label">Version actuelle :</div>
                <div class="info-value">{{ $parametres['version'] ?? '1.0.0' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Environnement :</div>
                <div class="info-value">{{ $parametres['environment'] ?? config('app.env') ?? 'local' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Fuseau horaire :</div>
                <div class="info-value">{{ $parametres['fuseau_horaire'] ?? config('app.timezone') ?? 'UTC' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Dernière mise à jour :</div>
                <div class="info-value">{{ $parametres['derniere_maj'] ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

<script>
    function showNotification(message, isSuccess = true) {
        let notif = document.getElementById('notification');
        if (!notif) {
            notif = document.createElement('div');
            notif.id = 'notification';
            notif.style.position = 'fixed';
            notif.style.top = '1rem';
            notif.style.right = '1rem';
            notif.style.padding = '1rem 1.2rem';
            notif.style.borderRadius = '1rem';
            notif.style.fontWeight = '600';
            notif.style.zIndex = 9999;
            document.body.appendChild(notif);
        }

        notif.style.display = 'block';
        notif.style.borderLeft = '4px solid';
        notif.style.borderLeftColor = isSuccess ? '#2ef75b' : '#ff5e7c';
        notif.style.background = isSuccess ? 'rgba(46, 247, 91, 0.12)' : 'rgba(255, 94, 124, 0.12)';
        notif.style.color = isSuccess ? '#2ef75b' : '#ff5e7c';
        notif.textContent = message;

        clearTimeout(window.paramNotificationTimeout);
        window.paramNotificationTimeout = setTimeout(() => {
            notif.style.display = 'none';
        }, 4000);
    }

    document.getElementById('general-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enregistrement...';

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

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
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showNotification('❌ Erreur de connexion', false);
        });
    });

    function downloadBackup() {
        if (confirm('Télécharger une sauvegarde du système ?')) {
            window.location.href = '{{ route("parametres.backup.download") }}';
        }
    }
</script>
@endsection
