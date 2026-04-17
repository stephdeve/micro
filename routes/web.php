<?php

use App\Http\Controllers\InterfaceModelController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouteurController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\SecuriteController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminReseauController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\EmployeController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\MikroTikApiController;
use App\Http\Controllers\MikroTikInterfaceController;
use App\Http\Controllers\MikroTikRouteController;
use App\Http\Controllers\MikroTikFirewallController;
use App\Http\Controllers\WifiZoneController;
use App\Http\Controllers\EmployeNetworkController;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    // Dashboard (redirige selon le rôle)
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== PROFIL =====
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
        Route::post('/avatar/delete', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ===== SUPER ADMIN =====
    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    });

    // ===== ADMINISTRATEUR RÉSEAU =====
    Route::middleware('role:admin_reseau|super_admin')->prefix('admin-reseau')->name('admin-reseau.')->group(function () {
        Route::get('dashboard', [AdminReseauController::class, 'dashboard'])->name('dashboard');

        // Firewall
        Route::get('routeurs/{routeur}/firewall', [AdminReseauController::class, 'firewallIndex'])->name('firewall');
        Route::post('routeurs/{routeur}/firewall', [AdminReseauController::class, 'firewallStore'])->name('firewall.store');
        Route::delete('routeurs/{routeur}/firewall/{ruleId}', [AdminReseauController::class, 'firewallDestroy'])->name('firewall.destroy');
        Route::post('routeurs/{routeur}/firewall/{ruleId}/toggle', [AdminReseauController::class, 'firewallToggle'])->name('firewall.toggle');
        // Firewall Filter
        Route::post('routeurs/{routeur}/firewall/filter', [MikroTikFirewallController::class, 'storeFilter'])->name('firewall.filter.store');
        Route::put('routeurs/{routeur}/firewall/filter/{rule}', [MikroTikFirewallController::class, 'updateFilter'])->name('firewall.filter.update');
        Route::delete('routeurs/{routeur}/firewall/filter/{rule}', [MikroTikFirewallController::class, 'destroyFilter'])->name('firewall.filter.destroy');
        Route::post('routeurs/{routeur}/firewall/filter/{rule}/enable', [MikroTikFirewallController::class, 'toggleFilter'])->name('firewall.filter.enable')->defaults('enable', true);
        Route::post('routeurs/{routeur}/firewall/filter/{rule}/disable', [MikroTikFirewallController::class, 'toggleFilter'])->name('firewall.filter.disable')->defaults('enable', false);
        Route::post('routeurs/{routeur}/firewall/filter/{rule}/move', [MikroTikFirewallController::class, 'moveFilter'])->name('firewall.filter.move');
        // Firewall NAT
        Route::post('routeurs/{routeur}/firewall/nat', [MikroTikFirewallController::class, 'storeNat'])->name('firewall.nat.store');
        Route::put('routeurs/{routeur}/firewall/nat/{rule}', [MikroTikFirewallController::class, 'updateNat'])->name('firewall.nat.update');
        Route::delete('routeurs/{routeur}/firewall/nat/{rule}', [MikroTikFirewallController::class, 'destroyNat'])->name('firewall.nat.destroy');
        Route::post('routeurs/{routeur}/firewall/nat/{rule}/enable', [MikroTikFirewallController::class, 'toggleNat'])->name('firewall.nat.enable')->defaults('enable', true);
        Route::post('routeurs/{routeur}/firewall/nat/{rule}/disable', [MikroTikFirewallController::class, 'toggleNat'])->name('firewall.nat.disable')->defaults('enable', false);
        Route::post('routeurs/{routeur}/firewall/nat/{rule}/move', [MikroTikFirewallController::class, 'moveNat'])->name('firewall.nat.move');
        // Firewall Mangle
        Route::post('routeurs/{routeur}/firewall/mangle', [MikroTikFirewallController::class, 'storeMangle'])->name('firewall.mangle.store');
        Route::put('routeurs/{routeur}/firewall/mangle/{rule}', [MikroTikFirewallController::class, 'updateMangle'])->name('firewall.mangle.update');
        Route::delete('routeurs/{routeur}/firewall/mangle/{rule}', [MikroTikFirewallController::class, 'destroyMangle'])->name('firewall.mangle.destroy');
        Route::post('routeurs/{routeur}/firewall/mangle/{rule}/enable', [MikroTikFirewallController::class, 'toggleMangle'])->name('firewall.mangle.enable')->defaults('enable', true);
        Route::post('routeurs/{routeur}/firewall/mangle/{rule}/disable', [MikroTikFirewallController::class, 'toggleMangle'])->name('firewall.mangle.disable')->defaults('enable', false);
        Route::post('routeurs/{routeur}/firewall/mangle/{rule}/move', [MikroTikFirewallController::class, 'moveMangle'])->name('firewall.mangle.move');

        // WiFi
        Route::get('routeurs/{routeur}/wifi', [AdminReseauController::class, 'wifiIndex'])->name('wifi');
        Route::post('routeurs/{routeur}/wifi/ssid', [AdminReseauController::class, 'wifiUpdateSsid'])->name('wifi.ssid');
        // WiFi Zones
        Route::get('routeurs/{routeur}/wifi-zones', [WifiZoneController::class, 'index'])->name('wifi-zones');
        Route::post('routeurs/{routeur}/wifi-zones', [WifiZoneController::class, 'store'])->name('wifi-zones.store');
        Route::get('routeurs/{routeur}/wifi-zones/{wifiZone}/show', [WifiZoneController::class, 'show'])->name('wifi-zones.show');
        Route::put('routeurs/{routeur}/wifi-zones/{wifiZone}', [WifiZoneController::class, 'update'])->name('wifi-zones.update');
        Route::delete('routeurs/{routeur}/wifi-zones/{wifiZone}', [WifiZoneController::class, 'destroy'])->name('wifi-zones.destroy');
        Route::post('routeurs/{routeur}/wifi-zones/{wifiZone}/toggle', [WifiZoneController::class, 'toggle'])->name('wifi-zones.toggle');
        Route::get('routeurs/{routeur}/wifi-zones/{wifiZone}/clients', [WifiZoneController::class, 'refreshClients'])->name('wifi-zones.clients');

        // Routes (IP)
        Route::get('routeurs/{routeur}/routes', [AdminReseauController::class, 'routesIndex'])->name('routes');
        Route::post('routeurs/{routeur}/routes/sync', [AdminReseauController::class, 'routesSync'])->name('routes.sync');
        Route::post('routeurs/{routeur}/routes', [AdminReseauController::class, 'routesStore'])->name('routes.store');
        Route::put('routeurs/{routeur}/routes/{routeId}', [AdminReseauController::class, 'routesUpdate'])->name('routes.update');
        Route::delete('routeurs/{routeur}/routes/{routeId}', [AdminReseauController::class, 'routesDestroy'])->name('routes.destroy');
        Route::post('routeurs/{routeur}/routes/{routeId}/enable', [AdminReseauController::class, 'routesEnable'])->name('routes.enable');
        Route::post('routeurs/{routeur}/routes/{routeId}/disable', [AdminReseauController::class, 'routesDisable'])->name('routes.disable');

        // Bande passante
        Route::get('routeurs/{routeur}/bandwidth', [AdminReseauController::class, 'bandwidthIndex'])->name('bandwidth');
        Route::post('routeurs/{routeur}/bandwidth', [AdminReseauController::class, 'bandwidthStore'])->name('bandwidth.store');
        Route::put('routeurs/{routeur}/bandwidth/{queueId}', [AdminReseauController::class, 'bandwidthUpdate'])->name('bandwidth.update');
        Route::delete('routeurs/{routeur}/bandwidth/{queueId}', [AdminReseauController::class, 'bandwidthDestroy'])->name('bandwidth.destroy');

        // DHCP
        Route::get('routeurs/{routeur}/dhcp', [AdminReseauController::class, 'dhcpIndex'])->name('dhcp');
        Route::post('routeurs/{routeur}/dhcp/servers', [AdminReseauController::class, 'dhcpStoreServer'])->name('dhcp.servers.store');
        Route::delete('routeurs/{routeur}/dhcp/servers/{server}', [AdminReseauController::class, 'dhcpDestroyServer'])->name('dhcp.servers.destroy');
        Route::post('routeurs/{routeur}/dhcp/networks', [AdminReseauController::class, 'dhcpStoreNetwork'])->name('dhcp.networks.store');
        Route::delete('routeurs/{routeur}/dhcp/networks/{network}', [AdminReseauController::class, 'dhcpDestroyNetwork'])->name('dhcp.networks.destroy');
    });

    // MikroTik API Routes (hors prefix admin-reseau pour URL plus simple)
    Route::middleware('role:admin_reseau|super_admin')->group(function () {
        Route::post('mikrotik/test-connection', [MikroTikApiController::class, 'testConnection'])->name('mikrotik.test');
        Route::get('mikrotik/routeurs/{routeur}/realtime', [MikroTikApiController::class, 'realtimeData'])->name('mikrotik.realtime');
        Route::post('mikrotik/routeurs/{routeur}/command', [MikroTikApiController::class, 'executeCommand'])->name('mikrotik.command');
    });

    // ===== ADMINISTRATEUR DE SERVICE =====
    Route::middleware('role:admin_service|super_admin')->prefix('admin-service')->name('admin-service.')->group(function () {
        Route::get('dashboard', [AdminServiceController::class, 'dashboard'])->name('dashboard');
        Route::get('employes', [AdminServiceController::class, 'employesIndex'])->name('employes');
        Route::get('employes/create', [AdminServiceController::class, 'employesCreate'])->name('employes.create');
        Route::post('employes', [AdminServiceController::class, 'employesStore'])->name('employes.store');
        Route::get('employes/{employe}/edit', [AdminServiceController::class, 'employesEdit'])->name('employes.edit');
        Route::put('employes/{employe}', [AdminServiceController::class, 'employesUpdate'])->name('employes.update');
        Route::delete('employes/{employe}', [AdminServiceController::class, 'employesDestroy'])->name('employes.destroy');
        Route::get('stats', [AdminServiceController::class, 'statsConsommation'])->name('stats');

        // Employés Réseau (WiFi)
        Route::get('employes-reseau', [AdminServiceController::class, 'employesReseauIndex'])->name('employes-reseau.index');
        Route::post('employes-reseau', [AdminServiceController::class, 'employesReseauStore'])->name('employes-reseau.store');
        Route::get('employes-reseau/{employe}/edit', [AdminServiceController::class, 'employesReseauEdit'])->name('employes-reseau.edit');
        Route::put('employes-reseau/{employe}', [AdminServiceController::class, 'employesReseauUpdate'])->name('employes-reseau.update');
        Route::delete('employes-reseau/{employe}', [AdminServiceController::class, 'employesReseauDestroy'])->name('employes-reseau.destroy');
        Route::post('employes-reseau/{employe}/toggle', [AdminServiceController::class, 'employesReseauToggle'])->name('employes-reseau.toggle');
        Route::get('employes-reseau/{employe}/realtime', [AdminServiceController::class, 'employesReseauRealtime'])->name('employes-reseau.realtime');
        Route::get('employes-reseau/{employe}/history', [AdminServiceController::class, 'employesReseauHistory'])->name('employes-reseau.history');
    });

    // ===== EMPLOYÉ =====
    Route::middleware('role:employe|admin_service|admin_reseau|super_admin')->prefix('employe')->name('employe.')->group(function () {
        Route::get('dashboard', [EmployeController::class, 'dashboard'])->name('dashboard');
        Route::get('trafic', [EmployeController::class, 'monTrafic'])->name('trafic');
        Route::get('messagerie', [EmployeController::class, 'messagerie'])->name('messagerie');
    });

    // Routes des différents modules (existantes)
    Route::get('routeurs/data', [RouteurController::class, 'data'])->name('routeurs.data');
    Route::get('routeurs/export', [RouteurController::class, 'export'])->name('routeurs.export');
    Route::get('routeurs/print', [RouteurController::class, 'print'])->name('routeurs.print');
    Route::get('routeurs/{routeur}/sync', [RouteurController::class, 'sync'])->name('routeurs.sync');
    Route::post('routeurs/{routeur}/restart', [RouteurController::class, 'restart'])->name('routeurs.restart');

    // MikroTik Interface Management Routes
    Route::get('routeurs/{routeur}/interfaces', [MikroTikInterfaceController::class, 'index'])->name('routeurs.interfaces');
    Route::post('routeurs/{routeur}/interfaces/sync', [MikroTikInterfaceController::class, 'sync'])->name('routeurs.interfaces.sync');
    Route::post('routeurs/{routeur}/interfaces/{interface}/enable', [MikroTikInterfaceController::class, 'enable'])->name('routeurs.interfaces.enable');
    Route::post('routeurs/{routeur}/interfaces/{interface}/disable', [MikroTikInterfaceController::class, 'disable'])->name('routeurs.interfaces.disable');
    Route::post('routeurs/{routeur}/interfaces/{interface}/rename', [MikroTikInterfaceController::class, 'rename'])->name('routeurs.interfaces.rename');
    Route::post('routeurs/{routeur}/interfaces/{interface}/configure', [MikroTikInterfaceController::class, 'configure'])->name('routeurs.interfaces.configure');
    Route::post('routeurs/{routeur}/interfaces/{interface}/ip', [MikroTikInterfaceController::class, 'setIp'])->name('routeurs.interfaces.ip');
    Route::delete('routeurs/{routeur}/interfaces/ips/{address}', [MikroTikInterfaceController::class, 'removeIp'])->name('routeurs.interfaces.ip.remove');
    Route::get('routeurs/{routeur}/interfaces/{interface}/details', [MikroTikInterfaceController::class, 'details'])->name('routeurs.interfaces.details');
    Route::get('routeurs/{routeur}/interfaces/{interface}/realtime', [MikroTikInterfaceController::class, 'realtimeStats'])->name('routeurs.interfaces.realtime');

    // MikroTik Route Management Routes
    Route::get('routeurs/{routeur}/routes', [MikroTikRouteController::class, 'index'])->name('routeurs.routes');
    Route::post('routeurs/{routeur}/routes/sync', [MikroTikRouteController::class, 'sync'])->name('routeurs.routes.sync');
    Route::post('routeurs/{routeur}/routes', [MikroTikRouteController::class, 'store'])->name('routeurs.routes.store');
    Route::put('routeurs/{routeur}/routes/{route}', [MikroTikRouteController::class, 'update'])->name('routeurs.routes.update');
    Route::delete('routeurs/{routeur}/routes/{route}', [MikroTikRouteController::class, 'destroy'])->name('routeurs.routes.destroy');
    Route::post('routeurs/{routeur}/routes/{route}/enable', [MikroTikRouteController::class, 'enable'])->name('routeurs.routes.enable');
    Route::post('routeurs/{routeur}/routes/{route}/disable', [MikroTikRouteController::class, 'disable'])->name('routeurs.routes.disable');

    // MikroTik Firewall Management Routes
    Route::get('routeurs/{routeur}/firewall', [MikroTikFirewallController::class, 'index'])->name('routeurs.firewall');
    // Filter rules
    Route::post('routeurs/{routeur}/firewall/filter', [MikroTikFirewallController::class, 'storeFilter'])->name('routeurs.firewall.filter.store');
    Route::put('routeurs/{routeur}/firewall/filter/{rule}', [MikroTikFirewallController::class, 'updateFilter'])->name('routeurs.firewall.filter.update');
    Route::delete('routeurs/{routeur}/firewall/filter/{rule}', [MikroTikFirewallController::class, 'destroyFilter'])->name('routeurs.firewall.filter.destroy');
    Route::post('routeurs/{routeur}/firewall/filter/{rule}/enable', [MikroTikFirewallController::class, 'toggleFilter'])->name('routeurs.firewall.filter.enable')->defaults('enable', true);
    Route::post('routeurs/{routeur}/firewall/filter/{rule}/disable', [MikroTikFirewallController::class, 'toggleFilter'])->name('routeurs.firewall.filter.disable')->defaults('enable', false);
    Route::post('routeurs/{routeur}/firewall/filter/{rule}/move', [MikroTikFirewallController::class, 'moveFilter'])->name('routeurs.firewall.filter.move');
    // NAT rules
    Route::post('routeurs/{routeur}/firewall/nat', [MikroTikFirewallController::class, 'storeNat'])->name('routeurs.firewall.nat.store');
    Route::put('routeurs/{routeur}/firewall/nat/{rule}', [MikroTikFirewallController::class, 'updateNat'])->name('routeurs.firewall.nat.update');
    Route::delete('routeurs/{routeur}/firewall/nat/{rule}', [MikroTikFirewallController::class, 'destroyNat'])->name('routeurs.firewall.nat.destroy');
    Route::post('routeurs/{routeur}/firewall/nat/{rule}/enable', [MikroTikFirewallController::class, 'toggleNat'])->name('routeurs.firewall.nat.enable')->defaults('enable', true);
    Route::post('routeurs/{routeur}/firewall/nat/{rule}/disable', [MikroTikFirewallController::class, 'toggleNat'])->name('routeurs.firewall.nat.disable')->defaults('enable', false);
    Route::post('routeurs/{routeur}/firewall/nat/{rule}/move', [MikroTikFirewallController::class, 'moveNat'])->name('routeurs.firewall.nat.move');
    // Mangle rules
    Route::post('routeurs/{routeur}/firewall/mangle', [MikroTikFirewallController::class, 'storeMangle'])->name('routeurs.firewall.mangle.store');
    Route::put('routeurs/{routeur}/firewall/mangle/{rule}', [MikroTikFirewallController::class, 'updateMangle'])->name('routeurs.firewall.mangle.update');
    Route::delete('routeurs/{routeur}/firewall/mangle/{rule}', [MikroTikFirewallController::class, 'destroyMangle'])->name('routeurs.firewall.mangle.destroy');
    Route::post('routeurs/{routeur}/firewall/mangle/{rule}/enable', [MikroTikFirewallController::class, 'toggleMangle'])->name('routeurs.firewall.mangle.enable')->defaults('enable', true);
    Route::post('routeurs/{routeur}/firewall/mangle/{rule}/disable', [MikroTikFirewallController::class, 'toggleMangle'])->name('routeurs.firewall.mangle.disable')->defaults('enable', false);
    Route::post('routeurs/{routeur}/firewall/mangle/{rule}/move', [MikroTikFirewallController::class, 'moveMangle'])->name('routeurs.firewall.mangle.move');

    // WiFi Zones
    Route::get('routeurs/{routeur}/wifi-zones', [WifiZoneController::class, 'index'])->name('routeurs.wifi-zones');
    Route::post('routeurs/{routeur}/wifi-zones', [WifiZoneController::class, 'store'])->name('routeurs.wifi-zones.store');
    Route::get('routeurs/{routeur}/wifi-zones/{wifiZone}/show', [WifiZoneController::class, 'show'])->name('routeurs.wifi-zones.show');
    Route::put('routeurs/{routeur}/wifi-zones/{wifiZone}', [WifiZoneController::class, 'update'])->name('routeurs.wifi-zones.update');
    Route::delete('routeurs/{routeur}/wifi-zones/{wifiZone}', [WifiZoneController::class, 'destroy'])->name('routeurs.wifi-zones.destroy');
    Route::post('routeurs/{routeur}/wifi-zones/{wifiZone}/toggle', [WifiZoneController::class, 'toggle'])->name('routeurs.wifi-zones.toggle');
    Route::get('routeurs/{routeur}/wifi-zones/{wifiZone}/clients', [WifiZoneController::class, 'refreshClients'])->name('routeurs.wifi-zones.clients');

    // Employés / Utilisateurs réseau
    Route::get('routeurs/{routeur}/employes', [EmployeNetworkController::class, 'index'])->name('routeurs.employes.index');
    Route::post('routeurs/{routeur}/employes', [EmployeNetworkController::class, 'store'])->name('routeurs.employes.store');
    Route::get('routeurs/{routeur}/employes/{employe}/edit', [EmployeNetworkController::class, 'edit'])->name('routeurs.employes.edit');
    Route::put('routeurs/{routeur}/employes/{employe}', [EmployeNetworkController::class, 'update'])->name('routeurs.employes.update');
    Route::delete('routeurs/{routeur}/employes/{employe}', [EmployeNetworkController::class, 'destroy'])->name('routeurs.employes.destroy');
    Route::get('routeurs/{routeur}/employes/{employe}', [EmployeNetworkController::class, 'show'])->name('routeurs.employes.show');
    Route::post('routeurs/{routeur}/employes/{employe}/toggle', [EmployeNetworkController::class, 'toggle'])->name('routeurs.employes.toggle');
    Route::get('routeurs/{routeur}/employes/{employe}/realtime', [EmployeNetworkController::class, 'realtimeStats'])->name('routeurs.employes.realtime');

    Route::resource('routeurs', RouteurController::class);
    Route::get('interfaces/{interface}/toggle', [InterfaceModelController::class, 'toggle'])->name('interfaces.toggle');
    Route::get('interfaces/{interface}/graph', [InterfaceModelController::class, 'graph'])->name('interfaces.graph');
    Route::resource('interfaces', InterfaceModelController::class);

    // Messagerie interne chiffrée AES-256
    Route::get('messagerie', [MessageController::class, 'index'])->name('messagerie.index');
    Route::post('messagerie', [MessageController::class, 'store'])->name('messagerie.store');
    Route::post('messagerie/conversation', [MessageController::class, 'createConversation'])->name('messagerie.conversation.create');
    Route::post('messagerie/group', [MessageController::class, 'createGroup'])->name('messagerie.group.create');
    Route::get('messagerie/poll', [MessageController::class, 'poll'])->name('messagerie.poll');
    Route::get('messagerie/unread-count', [MessageController::class, 'unreadCount'])->name('messagerie.unread-count');
    Route::post('messagerie/search', [MessageController::class, 'search'])->name('messagerie.search');
    Route::delete('messagerie/{message}', [MessageController::class, 'destroy'])->name('messagerie.destroy');
    Route::get('messagerie/attachments/{attachment}/download', [MessageController::class, 'downloadAttachment'])->name('messagerie.attachment.download');

    Route::resource('users', UserController::class);

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('notifications/delete-read', [NotificationController::class, 'deleteRead'])->name('notifications.deleteRead');

    // Routes parametres spécifiques AVANT la ressource
    Route::put('parametres', [ParametreController::class, 'updateAll'])->name('parametres.updateAll');
    Route::get('parametres/backup/download', [ParametreController::class, 'downloadBackup'])->name('parametres.backup.download');
    Route::post('parametres/backup/restore', [ParametreController::class, 'restoreBackup'])->name('parametres.backup.restore');
    Route::post('parametres/check-updates', [ParametreController::class, 'checkUpdates'])->name('parametres.check-updates');
    Route::resource('parametres', ParametreController::class);
    
    // Routes Securite - spécifiques AVANT la ressource
    Route::get('securite/data', [SecuriteController::class, 'data'])->name('securite.data');
    Route::post('securite/firewall-rules', [SecuriteController::class, 'addFirewallRule'])->name('securite.firewall-rules.store');
    Route::delete('securite/sessions/{id}', [SecuriteController::class, 'destroySession'])->name('securite.sessions.destroy');
    Route::post('securite/alertes/{securite}/resolve', [SecuriteController::class, 'resolveAlerte'])->name('securite.alertes.resolve');
    Route::post('securite/alertes/{securite}/archive', [SecuriteController::class, 'archiveAlerte'])->name('securite.alertes.archive');
    Route::delete('securite/alertes/{securite}', [SecuriteController::class, 'deleteAlerte'])->name('securite.alertes.delete');
    
    // Ressource Securite générale
    Route::resource('securite', SecuriteController::class);
    
    // Statistiques
    Route::get('statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');

});

require __DIR__.'/auth.php';