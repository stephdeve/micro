<!-- Header avec dropdown profil dynamique -->
<div class="header">
    <h1><i class="fas fa-tachometer-alt" style="color: #2aa9ff;"></i> Tableau de bord</h1>
    <div class="header-actions">
        @yield('header_buttons')
        <div class="btn-icon" id="notificationBell" style="position: relative; cursor: pointer;">
            <i class="fas fa-bell"></i>
            <span id="notificationBadge" class="notification-badge" style="display:none;">0</span>
        </div>
        <div class="notification-dropdown" id="notificationDropdown" style="display:none; position: absolute; top: 42px; right: 60px; width: 340px; background: rgba(12, 18, 34, 0.95); border: 1px solid #355177; border-radius: 0.8rem; box-shadow: 0 10px 30px rgba(0,0,0,0.45); z-index: 1100;">
            <div style="padding: 0.8rem 1rem; border-bottom: 1px solid #2a3f5a; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600; color: #d6e7ff;">Notifications</span>
                <div>
                    <button id="markReadAll" style="border: none; background: transparent; color: #8bb6f5; cursor: pointer; margin-right: 0.6rem;" title="Marquer toutes comme lues">
                        <i class="fas fa-check-circle" style="font-size: 1.1rem;"></i>
                    </button>
                    <button id="deleteRead" style="border: none; background: transparent; color: #fa6b6b; cursor: pointer;" title="Supprimer les notifications lues">
                        <i class="fas fa-trash-alt" style="font-size: 1.1rem;"></i>
                    </button>
                </div>
            </div>
            <div id="notificationsList" style="max-height: 260px; overflow-y: auto;"></div>
            <div style="padding: 0.7rem; border-top: 1px solid #2a3f5a; text-align: center;">
                <a href="{{ route('notifications.index') }}" style="color:#81b8ff; text-decoration:none;"><i class="fas fa-chevron-right" style="margin-right:0.4rem;"></i>Voir toutes</a>
            </div>
        </div>

        <!-- Profil avec dropdown dynamique -->
        <div class="profile-dropdown">
            <div class="avatar-btn" id="profileBtn">
                {{ substr(Auth::user()->name ?? 'AD', 0, 2) }}
            </div>
            <div class="dropdown-menu" id="profileDropdown">
                <div style="padding: 0.8rem 1.5rem; border-bottom: 1px solid #2b4055;">
                    <div style="font-weight: 600;">{{ Auth::user()->name ?? 'Administrateur' }}</div>
                    <div style="font-size: 0.8rem; color: #8fb4ff; margin-top: 0.2rem;">
                        <i class="fas fa-shield-alt"></i> {{ Auth::user()->roles->first()->name ?? 'superuser' }}
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <i class="fas fa-user-circle"></i> Mon profil
                </a>
                <a href="{{ route('parametres.index') }}" class="dropdown-item">
                    <i class="fas fa-cog"></i> Paramètres
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}" id="logout-form-dropdown">
                    @csrf
                    <button type="submit" class="dropdown-item logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>