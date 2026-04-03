<!-- Sidebar -->
<div class="sidebar">
    <div class="logo-area">
        <i class="fas fa-network-wired logo-icon"></i>
        <div class="logo-text">
            <h2>NetAdmin</h2>
            <p>MikroTik Controller</p>
        </div>
    </div>

    <div class="user-info">
        <div class="user-avatar">{{ substr(Auth::user()->name ?? 'AD', 0, 2) }}</div>
        <div class="user-details">
            <h4>{{ Auth::user()->name ?? 'Admin' }}</h4>
            <span><i class="fas fa-shield-alt"></i> {{ Auth::user()->roles->first()->name ?? 'superuser' }}</span>
        </div>
    </div>

    <div class="nav-menu">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="{{ route('routeurs.index') }}" class="nav-item {{ request()->routeIs('routeurs.*') ? 'active' : '' }}">
            <i class="fas fa-network-wired"></i> Routeurs
        </a>
        <a href="{{ route('interfaces.index') }}" class="nav-item {{ request()->routeIs('interfaces.*') ? 'active' : '' }}">
            <i class="fas fa-wifi"></i> Interfaces
        </a>
        <a href="{{ route('messagerie.index') }}" class="nav-item {{ request()->routeIs('messagerie.*') ? 'active' : '' }}">
            <i class="fas fa-envelope"></i> Messagerie
            @php
                use App\Models\Message;
                $unreadCount = Message::where('receiver_id', Auth::id())->where('is_read', false)->count();
            @endphp
            @if($unreadCount > 0)
                <span class="badge-message">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="{{ route('statistiques.index') }}" class="nav-item {{ request()->routeIs('statistiques.*') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Statistiques
        </a>
        <a href="{{ route('securite.index') }}" class="nav-item {{ request()->routeIs('securite.*') ? 'active' : '' }}">
            <i class="fas fa-shield-alt"></i> Sécurité
        </a>
        <a href="{{ route('parametres.index') }}" class="nav-item {{ request()->routeIs('parametres.*') ? 'active' : '' }}">
            <i class="fas fa-cog"></i> Paramètres
        </a>
        @if(Auth::user()->hasRole('admin'))
            <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Gestion des employés
            </a>
        @endif
    </div>

    <!-- Déconnexion sidebar -->
    <div class="logout-sidebar">
        <form method="POST" action="{{ route('logout') }}" id="logout-form-sidebar">
            @csrf
            <button type="submit" class="logout-btn-sidebar">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>
</div>