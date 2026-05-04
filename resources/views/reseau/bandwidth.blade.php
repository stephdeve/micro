@extends('layouts.app')

@section('title', 'QoS - Profils de Bande Passante - ' . $routeur->nom)

@section('content')
@php
$profiles = $profiles ?? collect([]);
$defaultProfiles = $defaultProfiles ?? [
    ['nom' => 'Direction', 'download' => 50, 'upload' => 20, 'quota' => 0, 'color' => 'purple'],
    ['nom' => 'Techniciens', 'download' => 10, 'upload' => 5, 'quota' => 50, 'color' => 'cyan'],
    ['nom' => 'Stagiaires', 'download' => 3, 'upload' => 1, 'quota' => 10, 'color' => 'amber'],
    ['nom' => 'Invités', 'download' => 1, 'upload' => 0.5, 'quota' => 2, 'color' => 'rose'],
];
@endphp
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/25">
                    <i class="fas fa-tachometer-alt text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Gestion de Bande Passante</h1>
                    <p class="text-sm text-slate-400">QoS (Quality of Service) - Routeur: {{ $routeur->nom }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('admin-reseau.bandwidth.apply-all', $routeur) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white text-sm font-medium transition-all shadow-lg shadow-emerald-500/25 inline-flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i> Appliquer tous les profils
                </button>
            </form>
            <button onclick="openModal('add')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white text-sm font-medium transition-all shadow-lg shadow-cyan-500/25 inline-flex items-center gap-2">
                <i class="fas fa-plus"></i> Nouveau profil
            </button>
        </div>
    </div>

    <!-- Info QoS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-cyan-400 mb-3 flex items-center gap-2">
                <i class="fas fa-info-circle"></i> C'est quoi QoS ?
            </h3>
            <p class="text-sm text-slate-300 mb-3">
                QoS (Quality of Service) permet de contrôler qui utilise combien de bande passante. 
                Sans ça, un seul utilisateur peut saturer toute la connexion Internet.
            </p>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-lg">
                    <p class="text-rose-400 font-semibold mb-1"><i class="fas fa-times-circle"></i> SANS QoS</p>
                    <p class="text-slate-400">Un stagiaire télécharge un film → tout le monde est bloqué</p>
                </div>
                <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg">
                    <p class="text-emerald-400 font-semibold mb-1"><i class="fas fa-check-circle"></i> AVEC QoS</p>
                    <p class="text-slate-400">Chaque personne a sa limite, personne ne peut saturer le réseau</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-slate-300 mb-3">Statistiques</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Profils actifs</span>
                    <span class="text-lg font-bold text-emerald-400">{{ $profiles->where('active', true)->count() }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Profils inactifs</span>
                    <span class="text-lg font-bold text-slate-400">{{ $profiles->where('active', false)->count() }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-400">Total</span>
                    <span class="text-lg font-bold text-white">{{ $profiles->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Profils par défaut suggérés -->
    @if($profiles->isEmpty())
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-6">
        <h3 class="text-sm font-semibold text-amber-400 mb-4 flex items-center gap-2">
            <i class="fas fa-star"></i> Profils recommandés à créer
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($defaultProfiles as $default)
            <div class="p-4 bg-slate-900/50 rounded-lg border border-slate-700 hover:border-cyan-500/50 transition-colors cursor-pointer" onclick="prefillProfile({{ json_encode($default) }})">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full bg-{{ $default['color'] }}-400"></div>
                    <span class="font-medium text-white">{{ $default['nom'] }}</span>
                </div>
                <div class="text-xs text-slate-400 space-y-1">
                    <div><i class="fas fa-arrow-down text-cyan-400 w-4"></i> {{ $default['download'] }} Mbps</div>
                    <div><i class="fas fa-arrow-up text-amber-400 w-4"></i> {{ $default['upload'] }} Mbps</div>
                    <div><i class="fas fa-database text-purple-400 w-4"></i> {{ $default['quota'] > 0 ? $default['quota'] . ' Go' : 'Illimité' }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Liste des profils -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-700 bg-gradient-to-r from-cyan-500/10 to-blue-500/10">
            <h3 class="font-semibold text-white flex items-center gap-2">
                <i class="fas fa-list"></i> Profils de Bande Passante
            </h3>
        </div>
        
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($profiles as $profile)
                <div class="bg-slate-900/50 border border-slate-700 rounded-xl overflow-hidden hover:border-{{ $profile->color }}-500/50 transition-all" id="profile-{{ $profile->id }}">
                    <!-- Header -->
                    <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between" style="background: linear-gradient(135deg, rgba(var(--color-{{ $profile->color }}), 0.1), transparent);">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-{{ $profile->color }}-500/20 flex items-center justify-center">
                                <i class="fas fa-{{ $profile->priority <= 2 ? 'crown' : ($profile->priority <= 4 ? 'user-tie' : ($profile->priority <= 6 ? 'user' : 'user-clock')) }} text-{{ $profile->color }}-400 text-sm"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-white">{{ $profile->nom }}</h4>
                                <span class="text-xs text-slate-400">Priorité {{ $profile->priority }}/8</span>
                            </div>
                        </div>
                        <form action="{{ route('admin-reseau.bandwidth.toggle', [$routeur, $profile]) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="relative w-10 h-5 rounded-full transition-colors {{ $profile->active ? 'bg-emerald-500' : 'bg-slate-600' }}" title="{{ $profile->active ? 'Actif' : 'Inactif' }}">
                                <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform {{ $profile->active ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </form>
                    </div>

                    <!-- Body -->
                    <div class="p-4 space-y-3">
                        @if($profile->description)
                        <p class="text-xs text-slate-400">{{ $profile->description }}</p>
                        @endif

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2 bg-slate-800/50 rounded-lg">
                                <div class="text-slate-400 mb-1"><i class="fas fa-arrow-down mr-1"></i>Download</div>
                                <div class="text-lg font-bold text-cyan-400">{{ $profile->download_mbps }} Mbps</div>
                            </div>
                            <div class="p-2 bg-slate-800/50 rounded-lg">
                                <div class="text-slate-400 mb-1"><i class="fas fa-arrow-up mr-1"></i>Upload</div>
                                <div class="text-lg font-bold text-amber-400">{{ $profile->upload_mbps }} Mbps</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-xs p-2 bg-slate-800/30 rounded-lg">
                            <span class="text-slate-400"><i class="fas fa-database mr-1"></i>Quota</span>
                            <span class="font-medium text-white">{{ $profile->quotaFormatted() }}</span>
                        </div>

                        @if($profile->target_network)
                        <div class="flex items-center justify-between text-xs p-2 bg-slate-800/30 rounded-lg">
                            <span class="text-slate-400"><i class="fas fa-network-wired mr-1"></i>Réseau</span>
                            <code class="text-cyan-400 font-mono">{{ $profile->target_network }}</code>
                        </div>
                        @endif

                        <!-- Commande MikroTik -->
                        <div class="text-xs font-mono text-emerald-400 bg-black/30 p-2 rounded border border-emerald-500/20">
                            /queue simple add name={{ $profile->getQueueName() }} max-limit={{ $profile->getMikrotikMaxLimit() }} target={{ $profile->target_network ?: '0.0.0.0/0' }}
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-4 py-2 border-t border-slate-700 bg-slate-900/30 flex items-center justify-between">
                        <button onclick="showCommands({{ $profile->id }})" class="text-xs text-slate-400 hover:text-cyan-400 transition-colors flex items-center gap-1">
                            <i class="fas fa-terminal"></i> Voir commandes
                        </button>
                        <div class="flex items-center gap-2">
                            <button onclick="editProfile({{ $profile->id }})" class="w-7 h-7 rounded bg-slate-800 hover:bg-blue-500/20 text-slate-400 hover:text-blue-400 transition-all flex items-center justify-center">
                                <i class="fas fa-edit text-xs"></i>
                            </button>
                            <form action="{{ route('admin-reseau.bandwidth.destroy', [$routeur, $profile]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce profil ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-7 h-7 rounded bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-all flex items-center justify-center">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full text-center py-12 bg-slate-900/30 rounded-xl border-2 border-dashed border-slate-700">
                    <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-tachometer-alt text-slate-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-white mb-1">Aucun profil QoS configuré</h3>
                    <p class="text-sm text-slate-400 mb-4">Créez votre premier profil de bande passante</p>
                    <button onclick="openModal('add')" class="px-4 py-2 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-medium">
                        <i class="fas fa-plus mr-2"></i>Créer un profil
                    </button>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Add/Edit -->
<div id="profileModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="px-4 py-3 border-b border-slate-700 bg-gradient-to-r from-cyan-500/10 to-blue-500/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                    <i class="fas fa-tachometer-alt text-white text-sm"></i>
                </div>
                <h3 class="font-bold text-white" id="modalTitle">Nouveau Profil</h3>
            </div>
            <button onclick="closeModal()" class="w-7 h-7 rounded bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-all flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        
        <form id="profileForm" method="POST" class="p-4 space-y-4">
            @csrf
            <div id="methodField"></div>
            
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1">Nom du profil</label>
                    <input type="text" name="nom" id="nom" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-slate-400 mb-1">Description</label>
                    <textarea name="description" id="description" rows="2" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none"></textarea>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Download (Mbps)</label>
                    <input type="number" name="download_mbps" id="download_mbps" min="0" max="10000" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Upload (Mbps)</label>
                    <input type="number" name="upload_mbps" id="upload_mbps" min="0" max="10000" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Quota (Go)</label>
                    <input type="number" name="quota_gb" id="quota_gb" min="0" placeholder="0 = illimité" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Réseau cible (CIDR)</label>
                    <input type="text" name="target_network" id="target_network" placeholder="192.168.10.0/24" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Priorité (1-8)</label>
                    <input type="number" name="priority" id="priority" min="1" max="8" value="8" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                    <p class="text-[10px] text-slate-500 mt-1">1 = plus prioritaire</p>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Couleur</label>
                    <select name="color" id="color" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:border-cyan-500 focus:outline-none">
                        <option value="blue">Bleu</option>
                        <option value="emerald">Vert</option>
                        <option value="amber">Orange</option>
                        <option value="rose">Rouge</option>
                        <option value="purple">Violet</option>
                        <option value="cyan">Cyan</option>
                        <option value="indigo">Indigo</option>
                    </select>
                </div>
            </div>
            
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="active" id="active" value="1" checked class="rounded bg-slate-800 border-slate-700 text-cyan-500">
                <label for="active" class="text-sm text-slate-300">Activer immédiatement</label>
            </div>
            
            <div class="flex gap-2 pt-4 border-t border-slate-700">
                <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-medium transition-all border border-slate-700">
                    Annuler
                </button>
                <button type="submit" class="flex-1 px-4 py-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white text-sm font-medium transition-all shadow-lg shadow-cyan-500/25">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Commandes -->
<div id="commandsModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-2xl w-full max-h-[80vh] flex flex-col">
        <div class="px-4 py-3 border-b border-slate-700 bg-gradient-to-r from-emerald-500/10 to-cyan-500/10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-cyan-600 flex items-center justify-center">
                    <i class="fas fa-terminal text-white text-sm"></i>
                </div>
                <h3 class="font-bold text-white">Commandes RouterOS</h3>
            </div>
            <button onclick="closeCommandsModal()" class="w-7 h-7 rounded bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition-all flex items-center justify-center">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-4 bg-black/50">
            <div id="commandsContent" class="space-y-1 font-mono text-sm"></div>
        </div>
        <div class="px-4 py-3 border-t border-slate-700 bg-slate-900">
            <button onclick="closeCommandsModal()" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-sm font-medium transition-all border border-slate-700">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
    const BASE_URL = '{{ url('/') }}';
    const ROUTEUR_ID = {{ $routeur->id }};
    
    function openModal(mode, profileId = null) {
        const modal = document.getElementById('profileModal');
        const form = document.getElementById('profileForm');
        const title = document.getElementById('modalTitle');
        const methodField = document.getElementById('methodField');
        
        if (mode === 'edit' && profileId) {
            title.textContent = 'Modifier le Profil';
            form.action = `${BASE_URL}/admin-reseau/routeurs/${ROUTEUR_ID}/bandwidth/${profileId}`;
            methodField.innerHTML = '@method('PUT')';
            
            // Charger les données
            fetch(`${BASE_URL}/admin-reseau/routeurs/${ROUTEUR_ID}/bandwidth/${profileId}`)
                .then(r => r.json())
                .then(data => {
                    const p = data.profile;
                    document.getElementById('nom').value = p.nom;
                    document.getElementById('description').value = p.description || '';
                    document.getElementById('download_mbps').value = p.download_mbps;
                    document.getElementById('upload_mbps').value = p.upload_mbps;
                    document.getElementById('quota_gb').value = p.quota_gb || '';
                    document.getElementById('target_network').value = p.target_network || '';
                    document.getElementById('priority').value = p.priority;
                    document.getElementById('color').value = p.color;
                    document.getElementById('active').checked = p.active;
                });
        } else {
            title.textContent = 'Nouveau Profil';
            form.action = `${BASE_URL}/admin-reseau/routeurs/${ROUTEUR_ID}/bandwidth`;
            methodField.innerHTML = '';
            form.reset();
            document.getElementById('priority').value = 8;
            document.getElementById('color').value = 'blue';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closeModal() {
        const modal = document.getElementById('profileModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    function editProfile(profileId) {
        openModal('edit', profileId);
    }
    
    function showCommands(profileId) {
        fetch(`${BASE_URL}/admin-reseau/routeurs/${ROUTEUR_ID}/bandwidth/${profileId}/commands`)
            .then(r => r.json())
            .then(data => {
                const modal = document.getElementById('commandsModal');
                const content = document.getElementById('commandsContent');
                content.innerHTML = data.commands.map(cmd => {
                    const color = cmd.startsWith('#') ? 'text-amber-400' : (cmd.startsWith('/') ? 'text-emerald-400' : 'text-slate-400');
                    return `<div class="${color}">${cmd}</div>`;
                }).join('');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
    }
    
    function closeCommandsModal() {
        const modal = document.getElementById('commandsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    function prefillProfile(data) {
        openModal('add');
        document.getElementById('nom').value = data.nom;
        document.getElementById('download_mbps').value = data.download;
        document.getElementById('upload_mbps').value = data.upload;
        document.getElementById('quota_gb').value = data.quota;
        document.getElementById('color').value = data.color;
    }
    
    // Fermer modals avec Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal();
            closeCommandsModal();
        }
    });
</script>
@endsection
