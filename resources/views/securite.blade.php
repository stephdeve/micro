@extends('layouts.app')

@section('title', 'Sécurité - Centre de Contrôle')

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-400 via-cyan-400 to-blue-500 bg-clip-text text-transparent">
                <i class="fas fa-shield-alt mr-3"></i>Centre de Sécurité
            </h1>
            <p class="text-slate-400 mt-1">Surveillance et protection du réseau</p>
        </div>
        <button id="refresh-securite" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
            <i class="fas fa-sync-alt"></i>
            <span>Actualiser</span>
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Niveau de sécurité -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shield-virus text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">Santé</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $stats['niveau_securite'] ?? 90 }}%</div>
                <div class="text-emerald-400/70 text-sm mt-1">
                    {{ ($stats['niveau_securite'] ?? 90) >= 80 ? 'Excellent' : (($stats['niveau_securite'] ?? 90) >= 50 ? 'Moyen' : 'Faible') }}
                </div>
            </div>
        </div>

        <!-- Tentatives bloquées -->
        <div class="bg-gradient-to-br from-rose-500/10 to-rose-600/5 border border-rose-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-rose-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-rose-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-ban text-rose-400 text-xl"></i>
                    </div>
                    <span class="text-rose-400 text-sm font-medium">Bloquées</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['tentatives_bloc'] ?? 0) }}</div>
                <div class="text-rose-400/70 text-sm mt-1">+{{ $stats['tentatives_bloc_today'] ?? 0 }} aujourd'hui</div>
            </div>
        </div>

        <!-- Règles firewall -->
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-firewall text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Firewall</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['regles_firewall'] ?? 0) }}</div>
                <div class="text-amber-400/70 text-sm mt-1">Règles actives</div>
            </div>
        </div>

        <!-- Connexions TLS -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-lock text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">TLS</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($stats['connexions_tls'] ?? 0) }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">Connexions chiffrées</div>
            </div>
        </div>
    </div>

    <!-- 3D Security Shield -->
    <div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-cube text-emerald-400"></i>
                Bouclier de sécurité 3D
            </h3>
            <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
        </div>
        <div id="security3D" class="h-64 w-full"></div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Alertes Section -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-amber-400"></i>
                    Alertes récentes
                </h3>
                <span class="text-xs text-amber-400 bg-amber-500/20 px-2 py-1 rounded-lg">
                    {{ $stats['alertes_non_resolues'] ?? 0 }} non résolues
                </span>
            </div>
            
            <div class="p-4 space-y-3" id="alertes-list">
                @forelse($alertes as $alerte)
                @php
                    $severityColor = match($alerte->severite) {
                        'critique' => 'rose',
                        'haute' => 'amber',
                        default => 'emerald'
                    };
                    $severityIcon = match($alerte->severite) {
                        'critique' => 'skull-crosswalk',
                        'haute' => 'exclamation-triangle',
                        default => 'check-circle'
                    };
                @endphp
                <div class="bg-slate-900/50 border-l-4 border-{{ $severityColor }}-500 rounded-xl p-4 hover:bg-slate-800/50 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-{{ $severityIcon }} text-{{ $severityColor }}-400"></i>
                                <span class="font-medium text-white truncate">{{ $alerte->nom_evenement }}</span>
                                <span class="text-xs text-slate-400">{{ $alerte->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-400 mb-2">{{ Str::limit($alerte->description, 100) }}</p>
                            <div class="flex items-center gap-2 text-xs text-slate-500">
                                <span class="px-2 py-0.5 bg-slate-700 rounded text-{{ $severityColor }}-400">{{ ucfirst($alerte->statut) }}</span>
                                @if($alerte->resolu_a)
                                    <span>Résolu {{ $alerte->resolu_a->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            @if($alerte->statut != 'resolu')
                                <button class="mark-resolved p-2 hover:bg-emerald-500/20 rounded-lg text-slate-400 hover:text-emerald-400 transition" data-id="{{ $alerte->id }}" title="Marquer résolu">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            @endif
                            <button class="archive-alert p-2 hover:bg-amber-500/20 rounded-lg text-slate-400 hover:text-amber-400 transition" data-id="{{ $alerte->id }}" title="Archiver">
                                <i class="fas fa-archive"></i>
                            </button>
                            <button class="delete-alert p-2 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition" data-id="{{ $alerte->id }}" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check-circle text-emerald-400 text-2xl"></i>
                    </div>
                    <p class="text-slate-400">Aucune alerte récente</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($alertes->hasPages())
            <div class="p-4 border-t border-slate-700">
                <div class="flex items-center justify-center gap-2">
                    @if($alertes->onFirstPage())
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </span>
                    @else
                        <a href="{{ $alertes->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </a>
                    @endif
                    
                    <span class="text-sm text-slate-400">
                        Page {{ $alertes->currentPage() }} / {{ $alertes->lastPage() }}
                    </span>
                    
                    @if($alertes->hasMorePages())
                        <a href="{{ $alertes->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </a>
                    @else
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-800 text-slate-600 cursor-not-allowed">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Firewall Rules Section -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-firewall text-amber-400"></i>
                    Règles firewall
                </h3>
                <button id="add-firewall-rule" class="px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 rounded-lg text-sm transition flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    <span class="hidden sm:inline">Ajouter</span>
                </button>
            </div>
            
            <!-- Add Rule Form -->
            <div id="add-firewall-rule-form" class="hidden p-4 border-b border-slate-700 bg-slate-900/50">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <input type="text" id="fw-rule-nom" placeholder="Nom de la règle" 
                           class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500/50">
                    <select id="fw-rule-chain" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500/50">
                        <option value="input">input</option>
                        <option value="output">output</option>
                        <option value="forward">forward</option>
                        <option value="prerouting">prerouting</option>
                        <option value="postrouting">postrouting</option>
                    </select>
                    <select id="fw-rule-action" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-amber-500/50">
                        <option value="accept">accept</option>
                        <option value="drop">drop</option>
                        <option value="reject">reject</option>
                        <option value="log">log</option>
                    </select>
                    <div class="flex gap-2">
                        <button id="save-firewall-rule" class="flex-1 px-3 py-2 bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 rounded-lg text-sm transition">
                            <i class="fas fa-save"></i>
                        </button>
                        <button id="cancel-firewall-rule" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 rounded-lg text-sm transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-4 max-h-96 overflow-y-auto" id="rule-list">
                @forelse($stats['regles_firewall_list'] ?? collect() as $rule)
                <div class="flex items-center gap-3 py-3 border-b border-slate-700/50 last:border-0">
                    <i class="fas fa-check-circle text-emerald-400"></i>
                    <div class="flex-1">
                        <span class="text-white font-medium">{{ $rule->nom ?? 'Sans nom' }}</span>
                        <span class="text-slate-400 text-sm ml-2">({{ $rule->chain ?? 'N/A' }})</span>
                    </div>
                    <span class="text-xs px-2 py-1 rounded bg-slate-700 text-slate-300">{{ $rule->action ?? 'accept' }}</span>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-slate-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shield-alt text-slate-500 text-2xl"></i>
                    </div>
                    <p class="text-slate-400">Aucune règle active</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sessions Actives -->
    <div class="mt-6 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-users text-cyan-400"></i>
                Sessions actives
            </h3>
            <span class="text-xs text-slate-400">{{ count($stats['sessions_actives'] ?? []) }} session(s)</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Utilisateur</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">IP</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Type</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Debut</th>
                        <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Duree</th>
                        <th class="text-center px-4 py-3 text-sm font-medium text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($stats['sessions_actives'] ?? collect() as $session)
                    @php
                        $startTime = $session->last_activity ? \Carbon\Carbon::createFromTimestamp($session->last_activity) : null;
                        $dur = $startTime ? $startTime->diffForHumans(['parts' => 2, 'short' => true]) : '-';
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-cyan-500/20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user text-cyan-400 text-sm"></i>
                                </div>
                                <span class="text-white">{{ $session->user_name ?? ($session->user_email ?? 'Invite') }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-400 font-mono text-sm">{{ $session->ip_address ?? 'N/A' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-1 rounded-full {{ $session->user_agent && \Illuminate\Support\Str::contains($session->user_agent, 'MikroTik') ? 'bg-emerald-500/20 text-emerald-400' : 'bg-cyan-500/20 text-cyan-400' }}">
                                {{ $session->user_agent && \Illuminate\Support\Str::contains($session->user_agent, 'MikroTik') ? 'MikroTik' : 'Web' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-sm">{{ $startTime ? $startTime->format('H:i:s') : '-' }}</td>
                        <td class="px-4 py-3 text-slate-400 text-sm">{{ $dur }}</td>
                        <td class="px-4 py-3 text-center">
                            <button class="p-2 hover:bg-rose-500/20 rounded-lg text-slate-400 hover:text-rose-400 transition" title="Deconnecter">
                                <i class="fas fa-ban"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                            Aucune session active detectee
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Three.js for 3D Security Shield -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function() {
    const container = document.getElementById('security3D');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1e293b);
    
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.z = 5;
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 2);
    scene.add(ambientLight);
    
    const pointLight1 = new THREE.PointLight(0x10b981, 2, 50);
    pointLight1.position.set(5, 5, 5);
    scene.add(pointLight1);
    
    const pointLight2 = new THREE.PointLight(0x06b6d4, 2, 50);
    pointLight2.position.set(-5, -5, 5);
    scene.add(pointLight2);

    // Shield Geometry - Icosahedron for shield shape
    const shieldGeometry = new THREE.IcosahedronGeometry(1.5, 1);
    const shieldMaterial = new THREE.MeshPhongMaterial({ 
        color: 0x10b981,
        emissive: 0x059669,
        emissiveIntensity: 0.3,
        shininess: 100,
        transparent: true,
        opacity: 0.9,
        wireframe: false
    });
    const shield = new THREE.Mesh(shieldGeometry, shieldMaterial);
    scene.add(shield);

    // Wireframe overlay
    const wireframeGeometry = new THREE.IcosahedronGeometry(1.52, 1);
    const wireframeMaterial = new THREE.MeshBasicMaterial({
        color: 0x34d399,
        transparent: true,
        opacity: 0.3,
        wireframe: true
    });
    const wireframe = new THREE.Mesh(wireframeGeometry, wireframeMaterial);
    scene.add(wireframe);

    // Orbiting particles representing threats
    const particles = [];
    const particleCount = 8;
    
    for (let i = 0; i < particleCount; i++) {
        const geometry = new THREE.SphereGeometry(0.1, 8, 8);
        const material = new THREE.MeshBasicMaterial({ 
            color: i < 2 ? 0xf43f5e : 0x10b981, // Red for threats, green for safe
            transparent: true,
            opacity: 0.8
        });
        const particle = new THREE.Mesh(geometry, material);
        
        const angle = (2 * Math.PI / particleCount) * i;
        const radius = 3 + Math.random() * 0.5;
        particle.position.x = radius * Math.cos(angle);
        particle.position.y = radius * Math.sin(angle);
        particle.position.z = (Math.random() - 0.5) * 2;
        
        particle.userData = {
            angle: angle,
            radius: radius,
            speed: 0.01 + Math.random() * 0.01,
            isThreat: i < 2
        };
        
        scene.add(particle);
        particles.push(particle);
    }

    // Connections between shield and particles
    const lines = [];
    particles.forEach(particle => {
        const lineGeometry = new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(0, 0, 0),
            particle.position
        ]);
        const lineMaterial = new THREE.LineBasicMaterial({ 
            color: particle.userData.isThreat ? 0xf43f5e : 0x10b981,
            transparent: true,
            opacity: 0.2
        });
        const line = new THREE.Line(lineGeometry, lineMaterial);
        scene.add(line);
        lines.push(line);
    });

    // Animation
    let isActive = true;
    const animate = () => {
        if (!isActive) return;
        requestAnimationFrame(animate);

        // Rotate shield
        shield.rotation.y += 0.005;
        shield.rotation.x += 0.002;
        wireframe.rotation.y += 0.005;
        wireframe.rotation.x += 0.002;

        // Pulse shield
        const scale = 1 + Math.sin(Date.now() * 0.001) * 0.05;
        shield.scale.set(scale, scale, scale);

        // Animate particles
        particles.forEach((particle, i) => {
            particle.userData.angle += particle.userData.speed;
            const r = particle.userData.radius;
            particle.position.x = r * Math.cos(particle.userData.angle);
            particle.position.y = r * Math.sin(particle.userData.angle);
            
            // Update line
            lines[i].geometry.setFromPoints([
                new THREE.Vector3(0, 0, 0),
                particle.position
            ]);
        });

        renderer.render(scene, camera);
    };

    animate();

    // Handle resize
    const handleResize = () => {
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    };
    window.addEventListener('resize', handleResize);

    // Cleanup on page hide
    document.addEventListener('visibilitychange', () => {
        isActive = document.visibilityState === 'visible';
        if (isActive) animate();
    });
})();

// Toggle firewall form
document.getElementById('add-firewall-rule')?.addEventListener('click', () => {
    const form = document.getElementById('add-firewall-rule-form');
    form.classList.toggle('hidden');
});

document.getElementById('cancel-firewall-rule')?.addEventListener('click', () => {
    document.getElementById('add-firewall-rule-form').classList.add('hidden');
});

// Refresh button
document.getElementById('refresh-securite')?.addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualiser';
    
    try {
        const res = await fetch('/securite/data', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        if (res.ok) {
            window.location.reload();
        }
    } catch (e) {
        console.error('Refresh error:', e);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync-alt"></i> Actualiser';
    }
});
</script>
@endsection
