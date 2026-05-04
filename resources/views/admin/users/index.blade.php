@extends('layouts.app')

@section('title', 'Gestion des Administrateurs - SuperAdmin')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center shadow-lg shadow-amber-500/25">
                    <i class="fas fa-user-shield text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Gestion des Administrateurs</h1>
                    <p class="text-sm text-slate-400">Créer et gérer les admin réseaux et admin services</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white text-sm font-medium transition-all shadow-lg shadow-cyan-500/25 inline-flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Nouvel Admin
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Admin Réseau</p>
                    <p class="text-2xl font-bold text-cyan-400">{{ $adminReseauUsers->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                    <i class="fas fa-network-wired text-cyan-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Admin Service</p>
                    <p class="text-2xl font-bold text-purple-400">{{ $adminServiceUsers->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center">
                    <i class="fas fa-users-cog text-purple-400"></i>
                </div>
            </div>
        </div>
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400">Super Admins</p>
                    <p class="text-2xl font-bold text-amber-400">{{ $superAdmins->count() }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center">
                    <i class="fas fa-crown text-amber-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Admin Réseau -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-700 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-network-wired text-cyan-400"></i>
                <h3 class="font-semibold text-white">Administrateurs Réseau</h3>
                <span class="px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-400 text-xs">{{ $adminReseauUsers->count() }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Utilisateur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Créé le</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($adminReseauUsers as $user)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                                    <i class="fas fa-user text-cyan-400 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-slate-400">{{ $user->telephone ?: 'N/A' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $user->est_actif ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                {{ $user->est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-slate-400">{{ $user->created_at->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-7 h-7 rounded bg-slate-800 hover:bg-{{ $user->est_actif ? 'rose' : 'emerald' }}-500/20 text-{{ $user->est_actif ? 'rose' : 'emerald' }}-400 transition-all" title="{{ $user->est_actif ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas fa-power-off text-xs"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.users.edit', $user) }}" class="w-7 h-7 rounded bg-slate-800 hover:bg-blue-500/20 text-blue-400 transition-all flex items-center justify-center" title="Modifier">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <button onclick="resetPassword({{ $user->id }}, '{{ $user->name }}')" class="w-7 h-7 rounded bg-slate-800 hover:bg-amber-500/20 text-amber-400 transition-all" title="Réinitialiser mot de passe">
                                    <i class="fas fa-key text-xs"></i>
                                </button>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded bg-slate-800 hover:bg-rose-500/20 text-rose-400 transition-all" title="Supprimer">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Aucun administrateur réseau</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section Admin Service -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-700 bg-gradient-to-r from-purple-500/10 to-pink-500/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-users-cog text-purple-400"></i>
                <h3 class="font-semibold text-white">Administrateurs Service</h3>
                <span class="px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-400 text-xs">{{ $adminServiceUsers->count() }}</span>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Utilisateur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Contact</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Créé le</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($adminServiceUsers as $user)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center">
                                    <i class="fas fa-user text-purple-400 text-sm"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-slate-400">{{ $user->telephone ?: 'N/A' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $user->est_actif ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                {{ $user->est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-slate-400">{{ $user->created_at->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <form action="{{ route('admin.users.toggle', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-7 h-7 rounded bg-slate-800 hover:bg-{{ $user->est_actif ? 'rose' : 'emerald' }}-500/20 text-{{ $user->est_actif ? 'rose' : 'emerald' }}-400 transition-all" title="{{ $user->est_actif ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas fa-power-off text-xs"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.users.edit', $user) }}" class="w-7 h-7 rounded bg-slate-800 hover:bg-blue-500/20 text-blue-400 transition-all flex items-center justify-center" title="Modifier">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <button onclick="resetPassword({{ $user->id }}, '{{ $user->name }}')" class="w-7 h-7 rounded bg-slate-800 hover:bg-amber-500/20 text-amber-400 transition-all" title="Réinitialiser mot de passe">
                                    <i class="fas fa-key text-xs"></i>
                                </button>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet utilisateur ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-7 h-7 rounded bg-slate-800 hover:bg-rose-500/20 text-rose-400 transition-all" title="Supprimer">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-2"></i>
                            <p>Aucun administrateur service</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Super Admins (lecture seule) -->
    <div class="bg-slate-800/30 border border-slate-700/50 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-700/50 bg-gradient-to-r from-amber-500/10 to-orange-500/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-crown text-amber-400"></i>
                <h3 class="font-semibold text-white">Super Administrateurs</h3>
                <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 text-xs">{{ $superAdmins->count() }}</span>
            </div>
            <span class="text-xs text-slate-500"><i class="fas fa-lock mr-1"></i> Lecture seule</span>
        </div>
        <div class="p-4">
            <div class="flex flex-wrap gap-3">
                @foreach($superAdmins as $admin)
                <div class="flex items-center gap-2 px-3 py-2 bg-slate-900/50 rounded-lg border border-slate-700/50">
                    <div class="w-6 h-6 rounded bg-amber-500/20 flex items-center justify-center">
                        <i class="fas fa-crown text-amber-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $admin->name }}</p>
                        <p class="text-xs text-slate-500">{{ $admin->email }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="resetPasswordModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-md w-full">
        <div class="px-4 py-3 border-b border-slate-700 bg-gradient-to-r from-amber-500/10 to-orange-500/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                    <i class="fas fa-key text-white text-sm"></i>
                </div>
                <h3 class="font-bold text-white">Réinitialiser le mot de passe</h3>
            </div>
            <button onclick="closeResetModal()" class="w-7 h-7 rounded bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-all flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="p-4 space-y-4">
            <p class="text-sm text-slate-400">Nouveau mot de passe pour <strong id="resetUserName" class="text-white"></strong></p>
            <div class="space-y-2">
                <div class="flex gap-2">
                    <input type="text" id="newPassword" class="flex-1 px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm font-mono focus:border-cyan-500 focus:outline-none" readonly>
                    <button onclick="generateNewPassword()" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-all border border-slate-700" title="Générer un nouveau">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button onclick="copyPassword()" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-all border border-slate-700" title="Copier">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="text-xs text-slate-500">Copiez ce mot de passe avant de l'enregistrer. Il ne sera plus visible.</p>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closeResetModal()" class="flex-1 px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-medium transition-all border border-slate-700">
                    Annuler
                </button>
                <button type="button" onclick="saveNewPassword()" class="flex-1 px-4 py-2 rounded-lg bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white text-sm font-medium transition-all shadow-lg shadow-amber-500/25">
                    Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentUserId = null;
    const BASE_URL = '{{ url('/') }}';
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    function resetPassword(userId, userName) {
        currentUserId = userId;
        document.getElementById('resetUserName').textContent = userName;
        generateNewPassword();
        document.getElementById('resetPasswordModal').classList.remove('hidden');
        document.getElementById('resetPasswordModal').classList.add('flex');
    }

    function closeResetModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
        document.getElementById('resetPasswordModal').classList.remove('flex');
        currentUserId = null;
    }

    function generateNewPassword() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        let password = '';
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('newPassword').value = password;
    }

    function copyPassword() {
        const input = document.getElementById('newPassword');
        input.select();
        document.execCommand('copy');
        
        const button = document.querySelector('button[onclick="copyPassword()"] i');
        button.className = 'fas fa-check text-emerald-400';
        setTimeout(() => {
            button.className = 'fas fa-copy';
        }, 2000);
    }

    function saveNewPassword() {
        if (!currentUserId) return;
        
        const password = document.getElementById('newPassword').value;
        
        fetch(`${BASE_URL}/admin/users/${currentUserId}/reset-password`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ password: password })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Mot de passe réinitialisé avec succès !');
                closeResetModal();
            } else {
                alert('Erreur: ' + (data.error || 'Une erreur est survenue'));
            }
        })
        .catch(e => {
            alert('Erreur de communication avec le serveur');
        });
    }

    // Fermer modal avec Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeResetModal();
        }
    });
</script>
@endsection
