@php
$user = Auth::user();
$role = $user?->roles?->first()?->name ?? 'user';
$unreadCount = \App\Models\MessageRecipient::where('user_id', $user?->id)->whereNull('read_at')->count();
@endphp

<!-- Sidebar Navigation -->
<div id="sidebar" class="fixed left-0 top-0 h-screen bg-slate-900 border-r border-slate-800 transition-all duration-300 z-50 flex flex-col" :class="collapsed ? 'w-20' : 'w-64'" x-data="{ collapsed: localStorage.getItem('sidebar-collapsed') === 'true' }" x-cloak>

    <!-- Toggle Button -->
    <button @click="collapsed = !collapsed; localStorage.setItem('sidebar-collapsed', collapsed)" class="absolute -right-3 top-20 w-6 h-6 bg-cyan-500 hover:bg-cyan-400 rounded-full flex items-center justify-center text-slate-900 shadow-lg transition-colors z-50">
        <i class="fas" :class="collapsed ? 'fa-chevron-right' : 'fa-chevron-left'" class="text-xs"></i>
    </button>

    <!-- Logo Area -->
    <div class="p-4 border-b border-slate-800 flex items-center gap-3">
        <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-network-wired text-white text-lg"></i>
        </div>
        <div x-show="!collapsed" x-transition class="overflow-hidden">
            <h2 class="font-bold text-white text-sm">NetAdmin</h2>
            <p class="text-xs text-slate-400">MikroTik</p>
        </div>
    </div>

    <!-- User Info -->
    <div class="p-4 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                {{ substr($user?->name ?? 'U', 0, 1) }}
            </div>
            <div x-show="!collapsed" x-transition class="overflow-hidden flex-1 min-w-0">
                <h4 class="text-sm font-medium text-white truncate">{{ $user?->name ?? 'User' }}</h4>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <i class="fas fa-shield-alt text-cyan-400"></i>
                    <span class="truncate">{{ $role }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('dashboard') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-tachometer-alt w-5 text-center"></i>
            <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Dashboard</span>
        </a>

        <!-- Super Admin -->
        @can('manage_all_services')
        <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('super-admin.*') ? 'bg-amber-500/20 text-amber-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-crown w-5 text-center"></i>
            <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Super Admin</span>
        </a>
        @endcan

        <!-- Admin Réseau -->
        @role('admin_reseau|super_admin')
        <div x-data="{ open: true }" class="relative">
            <button @click="open = !open" x-show="!collapsed" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('admin-reseau.*') || request()->routeIs('routeurs.*') || request()->routeIs('interfaces.*') || request()->routeIs('securite.*') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i class="fas fa-server w-5 text-center text-cyan-400"></i>
                    <span class="text-sm font-medium">Réseau</span>
                </div>
                <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? '' : '-rotate-90'"></i>
            </button>
            <a href="{{ route('admin-reseau.dashboard') }}" x-show="collapsed" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin-reseau.*') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-colors" title="Admin Réseau">
                <i class="fas fa-server w-5 text-center"></i>
            </a>
            <div x-show="(!collapsed && open) || collapsed" :class="collapsed ? 'space-y-1' : 'pl-11 space-y-1'" class="transition-all">
                <a href="{{ route('admin-reseau.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin-reseau.dashboard') ? 'text-cyan-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-chart-line w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('routeurs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('routeurs.*') ? 'text-cyan-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-network-wired w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Routeurs</span>
                </a>
                <a href="{{ route('interfaces.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('interfaces.*') ? 'text-cyan-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-ethernet w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Interfaces</span>
                </a>
                <a href="{{ route('securite.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('securite.*') ? 'text-cyan-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-shield-alt w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Sécurité</span>
                </a>
            </div>
        </div>
        @endrole

        <!-- Admin Service -->
        @can('manage_employees')
        <div x-data="{ open: false }" class="space-y-1">
            <button @click="open = !open" x-show="!collapsed" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                <div class="flex items-center gap-3">
                    <i class="fas fa-building w-5 text-center text-purple-400"></i>
                    <span class="text-sm font-medium">Service</span>
                </div>
                <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? '' : '-rotate-90'"></i>
            </button>
            <a href="{{ route('admin-service.dashboard') }}" x-show="collapsed" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin-service.*') ? 'bg-purple-500/20 text-purple-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition-colors" title="Mon Service">
                <i class="fas fa-building w-5 text-center"></i>
            </a>
            <div x-show="(!collapsed && open) || collapsed" :class="collapsed ? 'space-y-1' : 'pl-11 space-y-1'" class="transition-all" x-show="open" x-collapse>
                <a href="{{ route('admin-service.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin-service.dashboard') ? 'text-purple-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-chart-pie w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('admin-service.employes') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin-service.employes*') && !request()->routeIs('admin-service.employes-reseau*') ? 'text-purple-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-users-cog w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Employés</span>
                </a>
                <a href="{{ route('admin-service.employes-reseau.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin-service.employes-reseau*') ? 'text-purple-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-wifi w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Accès WiFi</span>
                </a>
                <a href="{{ route('admin-service.stats') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('admin-service.stats') ? 'text-purple-400' : 'text-slate-500 hover:text-slate-300' }} transition-colors" :class="collapsed ? 'justify-center' : ''">
                    <i class="fas fa-chart-bar w-4 text-center"></i>
                    <span x-show="!collapsed" class="whitespace-nowrap">Stats</span>
                </a>
            </div>
        </div>
        @endcan

        <!-- Employé -->
        @role('employe')
        <!-- Dashboard Employé -->
        <a href="{{ route('employe.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('employe.dashboard') ? 'bg-emerald-500/20 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-chart-pie w-5 text-center"></i>
            <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Mon Dashboard</span>
        </a>

        <!-- Mon Trafic -->
        <a href="{{ route('employe.trafic') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('employe.trafic') ? 'bg-emerald-500/20 text-emerald-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-signal w-5 text-center"></i>
            <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Mon Trafic</span>
        </a>

        <!-- Messagerie Employé -->
        <a href="{{ route('messagerie.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('messagerie.*') ? 'bg-amber-500/20 text-amber-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <div class="relative">
                <i class="fas fa-envelope w-5 text-center"></i>
                @if(($unreadCount ?? 0) > 0)
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold">{{ min($unreadCount, 9) }}</span>
                @endif
            </div>
            <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Messagerie</span>
        </a>
        @endrole

        <!-- Messagerie (tous les autres rôles sauf employé) -->
        @role('admin_reseau|admin_service|super_admin')
        <a href="{{ route('messagerie.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('messagerie.*') ? 'bg-amber-500/20 text-amber-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <div class="relative">
                <i class="fas fa-envelope w-5 text-center"></i>
                @if(($unreadCount ?? 0) > 0)
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold">{{ min($unreadCount, 9) }}</span>
                @endif
            </div>
            <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Messagerie</span>
        </a>
        @endrole

        <!-- Utilisateurs -->
        @can('manage_all_users')
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors {{ request()->routeIs('users.*') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
            <i class="fas fa-users w-5 text-center"></i>
            <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Utilisateurs</span>
        </a>
        @endcan
    </nav>

    <!-- Logout -->
    <div class="p-3 border-t border-slate-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-400 hover:bg-red-500/10 transition-colors group">
                <i class="fas fa-sign-out-alt w-5 text-center group-hover:scale-110 transition-transform"></i>
                <span x-show="!collapsed" x-transition class="text-sm font-medium whitespace-nowrap">Déconnexion</span>
            </button>
        </form>
    </div>
</div>

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden" onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')"></div>

<!-- Mobile Sidebar Toggle -->
<button onclick="document.getElementById('mobile-sidebar').classList.remove('-translate-x-full'); document.getElementById('sidebar-overlay').classList.remove('hidden')" class="lg:hidden fixed top-4 left-4 z-50 w-10 h-10 bg-slate-800 rounded-lg flex items-center justify-center text-white shadow-lg">
    <i class="fas fa-bars"></i>
</button>

<!-- Mobile Sidebar -->
<div id="mobile-sidebar" class="lg:hidden fixed left-0 top-0 h-screen w-64 bg-slate-900 border-r border-slate-800 -translate-x-full transition-transform duration-300 z-50 flex flex-col">
    <!-- Same content as desktop but always expanded -->
    <div class="p-4 border-b border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-network-wired text-white text-lg"></i>
            </div>
            <div>
                <h2 class="font-bold text-white text-sm">NetAdmin</h2>
                <p class="text-xs text-slate-400">MikroTik</p>
            </div>
        </div>
        <button onclick="document.getElementById('mobile-sidebar').classList.add('-translate-x-full'); document.getElementById('sidebar-overlay').classList.add('hidden')" class="text-slate-400 hover:text-white">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Mobile Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('dashboard') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800' }}">
            <i class="fas fa-tachometer-alt w-5"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        @role('admin_reseau|super_admin')
        <a href="{{ route('admin-reseau.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin-reseau.*') ? 'bg-cyan-500/20 text-cyan-400' : 'text-slate-400 hover:bg-slate-800' }}">
            <i class="fas fa-server w-5"></i>
            <span class="text-sm font-medium">Admin Réseau</span>
        </a>
        @endrole
        @can('manage_employees')
        <a href="{{ route('admin-service.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin-service.*') ? 'bg-purple-500/20 text-purple-400' : 'text-slate-400 hover:bg-slate-800' }}">
            <i class="fas fa-building w-5"></i>
            <span class="text-sm font-medium">Mon Service</span>
        </a>
        @endcan
        @auth
        <a href="{{ route('messagerie.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('messagerie.*') ? 'bg-amber-500/20 text-amber-400' : 'text-slate-400 hover:bg-slate-800' }}">
            <div class="relative">
                <i class="fas fa-envelope w-5"></i>
                @if(($unreadCount ?? 0) > 0)
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold">{{ min($unreadCount, 9) }}</span>
                @endif
            </div>
            <span class="text-sm font-medium">Messagerie</span>
        </a>
        @endauth
    </nav>

    <!-- Mobile Logout -->
    <div class="p-3 border-t border-slate-800">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-red-400 hover:bg-red-500/10">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="text-sm font-medium">Déconnexion</span>
            </button>
        </form>
    </div>
</div>

<!-- Alpine.js for interactivity -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    [x-cloak] { display: none !important; }
</style>
