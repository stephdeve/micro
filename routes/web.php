<?php

use App\Http\Controllers\InterfaceModelController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RouteurController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\SecuriteController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;

Route::get('/', function () {
    return view('auth.login');
});

// Routes d'authentification
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ===== PROFIL =====
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
        Route::post('/avatar/delete', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // Routes des différents modules
    Route::resource('routeurs', RouteurController::class);
    Route::get('routeurs/{routeur}/sync', [RouteurController::class, 'sync'])->name('routeurs.sync');
    Route::resource('interfaces', InterfaceModelController::class);
    Route::get('interfaces/{interface}/toggle', [InterfaceModelController::class, 'toggle'])->name('interfaces.toggle');
    Route::get('interfaces/{interface}/graph', [InterfaceModelController::class, 'graph'])->name('interfaces.graph');
    Route::resource('messagerie', MessageController::class);

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::post('notifications/delete-read', [NotificationController::class, 'deleteRead'])->name('notifications.deleteRead');
    Route::post('messagerie/{messagerie}/star', [MessageController::class, 'toggleStar'])->name('messagerie.star');
    Route::post('messagerie/{messagerie}/archive', [MessageController::class, 'archive'])->name('messagerie.archive');
    Route::post('messagerie/{messagerie}/restore', [MessageController::class, 'restore'])->name('messagerie.restore');
    Route::get('messagerie/{messagerie}/attachments/{attachment}/download', [MessageController::class, 'downloadAttachment'])->name('messagerie.attachments.download');
    Route::resource('parametres', ParametreController::class);
    Route::put('parametres', [ParametreController::class, 'updateAll'])->name('parametres.updateAll');
    Route::resource('securite', SecuriteController::class);
    
    // Statistiques
    Route::get('statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');

    // Route de déconnexion
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

require __DIR__.'/auth.php';