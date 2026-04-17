@extends('layouts.app')

@section('title', 'Super Admin - Vue Globale')

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/25">
                <i class="fas fa-crown text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-amber-400 via-orange-400 to-rose-500 bg-clip-text text-transparent">
                    Super Admin
                </h1>
                <p class="text-slate-400 mt-1 flex items-center gap-2">
                    <i class="fas fa-globe text-cyan-400/70"></i>
                    Vue globale de l'infrastructure
                </p>
            </div>
        </div>
        
        <div class="flex gap-3">
            <button onclick="refreshDashboard()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
                <span>Actualiser</span>
            </button>
            <span class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl font-medium flex items-center gap-2 shadow-lg shadow-amber-500/25">
                <i class="fas fa-shield-alt"></i>
                <span>Accès total</span>
            </span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <!-- Utilisateurs -->
        <div class="bg-gradient-to-br from-indigo-500/10 to-purple-600/5 border border-indigo-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-indigo-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-indigo-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-indigo-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-users text-indigo-400 text-xl"></i>
                    </div>
                    <span class="text-indigo-400 text-sm font-medium">Utilisateurs</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $totalUsers ?? 0 }}</div>
                <div class="text-indigo-400/70 text-sm mt-1">Total enregistrés</div>
            </div>
        </div>

        <!-- Services -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-building text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">Services</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $totalServices ?? 0 }}</div>
                <div class="text-emerald-400/70 text-sm mt-1">Départements actifs</div>
            </div>
        </div>

        <!-- Routeurs -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-network-wired text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Routeurs</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $routeursEnLigne ?? 0 }}/{{ $totalRouteurs ?? 0 }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">En ligne / Total</div>
            </div>
        </div>

        <!-- Alertes -->
        <div class="bg-gradient-to-br from-amber-500/10 to-orange-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Alertes</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $alertesActives ?? 0 }}</div>
                <div class="text-amber-400/70 text-sm mt-1">Nouvelles alertes</div>
            </div>
        </div>

        <!-- Incidents -->
        <div class="bg-gradient-to-br from-rose-500/10 to-pink-600/5 border border-rose-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-rose-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-rose-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-skull-crossbones text-rose-400 text-xl"></i>
                    </div>
                    <span class="text-rose-400 text-sm font-medium">Critiques</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ $incidentCritiques ?? 0 }}</div>
                <div class="text-rose-400/70 text-sm mt-1">Incidents 24h</div>
            </div>
        </div>
    </div>

    <!-- 3D Global Infrastructure Visualization -->
    <div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-cube text-amber-400"></i>
                Infrastructure 3D - Vue Globale
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-indigo-400"></span> Utilisateurs
                </span>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-emerald-400"></span> Services
                </span>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-cyan-400"></span> Routeurs
                </span>
                <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
            </div>
        </div>
        <div id="globalNetwork3D" class="h-80 w-full"></div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Network Performance -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-heartbeat text-cyan-400"></i>
                    Performance Réseau
                </h3>
            </div>
            <div class="p-4 space-y-4">
                <!-- CPU -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-400 flex items-center gap-2">
                            <i class="fas fa-microchip text-cyan-400"></i>
                            CPU Moyen
                        </span>
                        <span class="text-cyan-400 font-mono font-medium">{{ $globalPerf['cpu'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2 overflow-hidden">
                        @php
                            $cpuPct = min(100, $globalPerf['cpu'] ?? 0);
                            $cpuColor = $cpuPct > 80 ? 'bg-rose-400' : ($cpuPct > 60 ? 'bg-amber-400' : 'bg-cyan-400');
                        @endphp
                        <div class="{{ $cpuColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $cpuPct }}%"></div>
                    </div>
                </div>

                <!-- Memory -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-400 flex items-center gap-2">
                            <i class="fas fa-memory text-purple-400"></i>
                            Mémoire Moyenne
                        </span>
                        <span class="text-purple-400 font-mono font-medium">{{ $globalPerf['memory'] ?? 0 }}%</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2 overflow-hidden">
                        @php
                            $memPct = min(100, $globalPerf['memory'] ?? 0);
                            $memColor = $memPct > 80 ? 'bg-rose-400' : ($memPct > 60 ? 'bg-amber-400' : 'bg-purple-400');
                        @endphp
                        <div class="{{ $memColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $memPct }}%"></div>
                    </div>
                </div>

                <!-- Temperature -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-400 flex items-center gap-2">
                            <i class="fas fa-thermometer-half text-amber-400"></i>
                            Température
                        </span>
                        <span class="{{ ($globalPerf['temperature'] ?? 0) > 60 ? 'text-rose-400' : 'text-amber-400' }} font-mono font-medium">{{ $globalPerf['temperature'] ?? 0 }}°C</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-2 overflow-hidden">
                        @php
                            $tempPct = min(100, ($globalPerf['temperature'] ?? 0) / 80 * 100);
                            $tempColor = ($globalPerf['temperature'] ?? 0) > 60 ? 'bg-rose-400' : 'bg-amber-400';
                        @endphp
                        <div class="{{ $tempColor }} h-2 rounded-full transition-all duration-500" style="width: {{ $tempPct }}%"></div>
                    </div>
                </div>

                <!-- Bandwidth -->
                <div class="p-3 bg-slate-900/50 rounded-xl mt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-2">
                            <i class="fas fa-tachometer-alt text-emerald-400"></i>
                            Bande passante
                        </span>
                        <span class="text-lg font-bold text-white">{{ number_format($bandePassante / 1000000, 2) }} <span class="text-sm text-slate-400">Mbps</span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roles Distribution -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-user-tag text-indigo-400"></i>
                    Répartition des Rôles
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-48 flex items-center justify-center mb-4">
                    <canvas id="rolesChart" class="max-h-full"></canvas>
                </div>
                
                <div class="space-y-2">
                    @foreach($rolesStats ?? [] as $role => $count)
                        @php
                            $roleColor = match($role) {
                                'super_admin' => 'indigo',
                                'admin_reseau' => 'cyan',
                                'admin_service' => 'emerald',
                                default => 'slate'
                            };
                        @endphp
                        <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-{{ $roleColor }}-400"></span>
                                <span class="text-slate-300 text-sm">{{ str_replace('_', ' ', $role) }}</span>
                            </div>
                            <span class="text-{{ $roleColor }}-400 font-medium">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Trafic 7 jours -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-chart-area text-purple-400"></i>
                    Trafic 7 jours
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-48">
                    <canvas id="trafficChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Table -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
        <!-- Services List -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-building text-emerald-400"></i>
                    Services
                </h3>
                <span class="text-sm text-slate-400">{{ count($services ?? []) }} services</span>
            </div>
            
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-slate-900/50 sticky top-0">
                        <tr>
                            <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Service</th>
                            <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Code</th>
                            <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Responsable</th>
                            <th class="text-left px-4 py-3 text-sm font-medium text-slate-400">Employés</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @forelse($services ?? [] as $svc)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-building text-emerald-400 text-sm"></i>
                                    </div>
                                    <span class="text-white font-medium">{{ $svc->nom }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-slate-700 text-slate-300 rounded text-xs">{{ $svc->code }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $svc->responsable?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-emerald-400 font-medium">{{ $svc->employes_count }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                Aucun service disponible
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alertes -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-bell text-amber-400"></i>
                    Dernières Alertes
                </h3>
                <span class="text-sm text-slate-400">5 dernières</span>
            </div>
            
            <div class="p-4 space-y-3 max-h-80 overflow-y-auto">
                @forelse($dernieresAlertes ?? [] as $alerte)
                    @php
                        $alertColor = match($alerte->severite ?? 'nouveau') {
                            'critique' => 'rose',
                            'warn' => 'amber',
                            default => 'cyan'
                        };
                    @endphp
                    <div class="flex items-start gap-3 p-3 bg-slate-900/50 rounded-xl border border-slate-700 hover:border-{{ $alertColor }}-500/30 transition">
                        <div class="w-10 h-10 bg-{{ $alertColor }}-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-{{ $alertColor }}-400"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 bg-{{ $alertColor }}-500/20 text-{{ $alertColor }}-400 rounded text-xs uppercase">{{ $alerte->severite ?? 'nouveau' }}</span>
                                <span class="text-xs text-slate-400">{{ $alerte->created_at?->diffForHumans() ?? '—' }}</span>
                            </div>
                            <p class="text-slate-300 text-sm truncate">{{ $alerte->description ?? '—' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400">
                        <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-check-circle text-emerald-400 text-2xl"></i>
                        </div>
                        <p>Aucune alerte récente</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<!-- Three.js for Global Infrastructure Visualization -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    const container = document.getElementById('globalNetwork3D');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1e293b);
    scene.fog = new THREE.Fog(0x1e293b, 10, 50);
    
    const camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(0, 8, 12);
    camera.lookAt(0, 0, 0);
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 1.5);
    scene.add(ambientLight);
    
    const pointLight1 = new THREE.PointLight(0xffb700, 1.5, 30);
    pointLight1.position.set(10, 15, 10);
    scene.add(pointLight1);
    
    const pointLight2 = new THREE.PointLight(0x06b6d4, 1.2, 30);
    pointLight2.position.set(-10, 5, -10);
    scene.add(pointLight2);
    
    const pointLight3 = new THREE.PointLight(0x10b981, 1.2, 30);
    pointLight3.position.set(10, 5, -10);
    scene.add(pointLight3);

    // Device detection for performance optimization
    const isMobile = window.matchMedia('(pointer: coarse)').matches;
    const isLowPower = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 4;
    
    // Central hub (Super Admin) - Optimized geometry
    const hubGroup = new THREE.Group();
    
    // Main sphere - reduced segments
    const hubSegments = isMobile ? 12 : (isLowPower ? 16 : 24);
    const hubGeometry = new THREE.SphereGeometry(1.2, hubSegments, hubSegments);
    const hubMaterial = new THREE.MeshPhongMaterial({ 
        color: 0xffb700,
        emissive: 0xff8c00,
        emissiveIntensity: 0.3,
        shininess: 50  // Reduced shininess
    });
    const hub = new THREE.Mesh(hubGeometry, hubMaterial);
    hubGroup.add(hub);
    
    // Inner core - reduced segments
    const coreGeometry = new THREE.SphereGeometry(0.6, isMobile ? 8 : 12, isMobile ? 8 : 12);
    const coreMaterial = new THREE.MeshBasicMaterial({ 
        color: 0xffe4b5,
        transparent: true,
        opacity: 0.8
    });
    const core = new THREE.Mesh(coreGeometry, coreMaterial);
    hubGroup.add(core);
    
    // Crown ring - reduced segments
    const crownGeometry = new THREE.TorusGeometry(1.5, 0.1, isMobile ? 4 : 6, isMobile ? 16 : 24);
    const crownMaterial = new THREE.MeshBasicMaterial({ 
        color: 0xffb700,
        transparent: true,
        opacity: 0.6
    });
    const crown = new THREE.Mesh(crownGeometry, crownMaterial);
    crown.rotation.x = Math.PI / 2;
    crown.position.y = 0.5;
    hubGroup.add(crown);
    
    scene.add(hubGroup);
    
    // Data from PHP - with device-aware limits
    const totalUsers = {{ $totalUsers ?? 0 }};
    const totalServices = {{ $totalServices ?? 0 }};
    const totalRouteurs = {{ $totalRouteurs ?? 0 }};
    const routeursEnLigne = {{ $routeursEnLigne ?? 0 }};
    
    // Limit counts based on device capability
    const maxOrbitItems = isMobile ? 6 : (isLowPower ? 8 : 12);
    
    // Create orbiting elements - reduced counts
    const orbits = [
        { radius: 4, speed: 0.008, color: 0x818cf8, count: Math.min(totalUsers, maxOrbitItems), label: 'Utilisateurs' },
        { radius: 6, speed: 0.005, color: 0x34d399, count: Math.min(totalServices, Math.floor(maxOrbitItems/2)), label: 'Services' },
        { radius: 8, speed: 0.003, color: 0x22d3ee, count: Math.min(totalRouteurs, maxOrbitItems), label: 'Routeurs' }
    ];
    
    const orbitElements = [];
    
    orbits.forEach((orbit, orbitIndex) => {
        const orbitGroup = new THREE.Group();
        
        // Orbit ring
        const ringGeometry = new THREE.TorusGeometry(orbit.radius, 0.02, 8, 64);
        const ringMaterial = new THREE.MeshBasicMaterial({ 
            color: orbit.color,
            transparent: true,
            opacity: 0.2
        });
        const ring = new THREE.Mesh(ringGeometry, ringMaterial);
        ring.rotation.x = Math.PI / 2;
        orbitGroup.add(ring);
        
        // Satellites
        const satellites = [];
        for (let i = 0; i < Math.max(orbit.count, 3); i++) {
            const angle = (2 * Math.PI / Math.max(orbit.count, 3)) * i;
            
            // Satellite group
            const satGroup = new THREE.Group();
            
            // Main satellite body - simplified
            const satGeometry = new THREE.BoxGeometry(0.3, 0.3, 0.3);
            const satMaterial = new THREE.MeshPhongMaterial({ 
                color: orbit.color,
                emissive: orbit.color,
                emissiveIntensity: 0.2
            });
            const sat = new THREE.Mesh(satGeometry, satMaterial);
            satGroup.add(sat);
            
            // Glow - reduced segments
            const glowGeometry = new THREE.SphereGeometry(0.25, isMobile ? 6 : 8, isMobile ? 6 : 8);
            const glowMaterial = new THREE.MeshBasicMaterial({ 
                color: orbit.color,
                transparent: true,
                opacity: 0.25
            });
            const glow = new THREE.Mesh(glowGeometry, glowMaterial);
            satGroup.add(glow);
            
            // Connection line to center
            const lineGeometry = new THREE.BufferGeometry().setFromPoints([
                new THREE.Vector3(0, 0, 0),
                new THREE.Vector3(orbit.radius * Math.cos(angle), 0, orbit.radius * Math.sin(angle))
            ]);
            const lineMaterial = new THREE.LineBasicMaterial({ 
                color: orbit.color,
                transparent: true,
                opacity: 0.1
            });
            const line = new THREE.Line(lineGeometry, lineMaterial);
            orbitGroup.add(line);
            
            // Position satellite
            satGroup.position.set(
                orbit.radius * Math.cos(angle),
                0,
                orbit.radius * Math.sin(angle)
            );
            
            orbitGroup.add(satGroup);
            satellites.push({ mesh: satGroup, angle: angle });
        }
        
        // Slight tilt for visual interest
        orbitGroup.rotation.x = orbitIndex * 0.2;
        
        scene.add(orbitGroup);
        orbitElements.push({ 
            group: orbitGroup, 
            satellites: satellites, 
            speed: orbit.speed,
            radius: orbit.radius
        });
    });
    
    // Floating particles - reduced count
    const particleCount = isMobile ? 8 : (isLowPower ? 12 : 20);
    const particles = [];
    for (let i = 0; i < particleCount; i++) {
        const geometry = new THREE.SphereGeometry(0.05, 6, 6);  // Reduced segments
        const material = new THREE.MeshBasicMaterial({ 
            color: 0xffb700,
            transparent: true,
            opacity: 0.5  // Slightly reduced
        });
        const particle = new THREE.Mesh(geometry, material);
        
        const angle = Math.random() * Math.PI * 2;
        const r = 2 + Math.random() * 6;  // Reduced radius range
        particle.position.set(
            r * Math.cos(angle),
            (Math.random() - 0.5) * 3,  // Reduced Y range
            r * Math.sin(angle)
        );
        particle.userData = {
            angle: angle,
            radius: r,
            speed: 0.003 + Math.random() * 0.005,  // Reduced speed
            ySpeed: (Math.random() - 0.5) * 0.01,
            yOffset: Math.random() * Math.PI * 2
        };
        scene.add(particle);
        particles.push(particle);
    }
    
    // Animation with performance optimizations
    let isActive = true;
    let animationId = null;
    let frameCount = 0;
    const frameSkip = isMobile ? 2 : 1;  // Skip every other frame on mobile
    
    // Intersection Observer for lazy rendering
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            isActive = entry.isIntersecting;
            if (!isActive && animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            } else if (isActive && !animationId) {
                animate();
            }
        });
    }, { threshold: 0.1 });
    observer.observe(container);
    
    // Visibility API
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            isActive = false;
            if (animationId) {
                cancelAnimationFrame(animationId);
                animationId = null;
            }
        } else {
            isActive = true;
            if (!animationId) animate();
        }
    });
    
    const animate = () => {
        if (!isActive) return;
        animationId = requestAnimationFrame(animate);
        
        // Frame skipping
        frameCount++;
        if (frameCount % frameSkip !== 0) return;
        
        // Rotate hub
        hubGroup.rotation.y += 0.003;  // Reduced speed
        crown.rotation.z -= 0.006;
        
        // Rotate and update satellites
        orbitElements.forEach(orbit => {
            orbit.group.rotation.y += orbit.speed;
        });
        
        // Animate particles (every 3rd frame)
        if (frameCount % 3 === 0) {
            particles.forEach(p => {
                p.userData.angle += p.userData.speed;
                p.position.x = p.userData.radius * Math.cos(p.userData.angle);
                p.position.z = p.userData.radius * Math.sin(p.userData.angle);
                p.position.y += Math.sin(Date.now() * 0.001 + p.userData.yOffset) * 0.008;
            });
        }
        
        renderer.render(scene, camera);
    };
    
    animate();
    
    // Handle resize (debounced)
    let resizeTimeout;
    const handleResize = () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            camera.aspect = container.clientWidth / container.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(container.clientWidth, container.clientHeight);
        }, 100);
    };
    window.addEventListener('resize', handleResize);
})();

// 2D Charts with Chart.js
const rolesData = @json($rolesStats ?? []);
const trafficData = @json($traficSemaine ?? []);

// Roles Doughnut Chart - Optimisé
const ctxRoles = document.getElementById('rolesChart')?.getContext('2d');
if (ctxRoles && Object.keys(rolesData).length > 0) {
    const roleLabels = Object.keys(rolesData).map(r => r.replace('_', ' '));
    const roleValues = Object.values(rolesData);
    const roleColors = Object.keys(rolesData).map(r => {
        switch(r) {
            case 'super_admin': return '#818cf8';
            case 'admin_reseau': return '#22d3ee';
            case 'admin_service': return '#34d399';
            default: return '#64748b';
        }
    });
    
    new Chart(ctxRoles, {
        type: 'doughnut',
        data: {
            labels: roleLabels,
            datasets: [{
                data: roleValues,
                backgroundColor: roleColors,
                borderColor: '#1e293b',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            animation: { duration: 0 },
            plugins: {
                legend: { display: false }
            }
        }
    });
}

// Traffic Line Chart - Optimisé
const ctxTraffic = document.getElementById('trafficChart')?.getContext('2d');
if (ctxTraffic && trafficData.length > 0) {
    // Limit data points to 15 max
    const step = Math.ceil(trafficData.length / 15);
    const limitedData = trafficData.filter((_, i) => i % step === 0).slice(0, 15);
    
    const labels = limitedData.map(t => t.date.substring(5));
    const values = limitedData.map(t => t.total);
    
    new Chart(ctxTraffic, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Trafic',
                data: values,
                borderColor: '#a855f7',
                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointBackgroundColor: '#a855f7'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 0 },
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    ticks: { color: '#94a3b8', font: { size: 10 } },
                    grid: { color: '#334155' }
                },
                y: {
                    ticks: { color: '#94a3b8', font: { size: 10 } },
                    grid: { color: '#334155' },
                    beginAtZero: true
                }
            }
        }
    });
}

// Refresh function
function refreshDashboard() {
    const btn = document.getElementById('refresh-icon');
    btn.classList.add('fa-spin');
    setTimeout(() => {
        window.location.reload();
    }, 500);
}
</script>
@endsection
