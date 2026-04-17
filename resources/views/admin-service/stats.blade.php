@extends('layouts.app')

@section('title', 'Statistiques - ' . ($service->nom ?? 'Service'))

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin-service.dashboard') }}" class="p-3 bg-slate-800 hover:bg-slate-700 rounded-xl transition flex items-center justify-center">
                <i class="fas fa-arrow-left text-cyan-400"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-400 via-pink-400 to-rose-500 bg-clip-text text-transparent">
                    <i class="fas fa-chart-pie mr-3"></i>Statistiques
                </h1>
                <p class="text-slate-400 mt-1 flex items-center gap-2">
                    <i class="fas fa-building text-purple-400/70"></i>
                    Service : <span class="text-white font-medium">{{ $service->nom ?? 'Non assigné' }}</span>
                </p>
            </div>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('admin-service.employes') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="fas fa-users text-cyan-400"></i>
                <span>Employés</span>
            </a>
            <button onclick="refreshData()" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-600 hover:from-purple-400 hover:to-pink-500 rounded-xl font-medium transition flex items-center gap-2 shadow-lg shadow-purple-500/25">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
                <span>Actualiser</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    @php
        $totalConsommation = $stats->sum('total') ?? 0;
        $avgDaily = count($stats ?? []) > 0 ? $totalConsommation / count($stats ?? []) : 0;
        $maxDaily = $stats->max('total') ?? 0;
        $types = $stats->groupBy('type');
    @endphp
    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Consommation -->
        <div class="bg-gradient-to-br from-purple-500/10 to-pink-600/5 border border-purple-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-purple-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-purple-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-database text-purple-400 text-xl"></i>
                    </div>
                    <span class="text-purple-400 text-sm font-medium">Total (30j)</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($totalConsommation, 0) }}</div>
                <div class="text-purple-400/70 text-sm mt-1">{{ $stats[0]->unite ?? 'Go' }}</div>
            </div>
        </div>

        <!-- Moyenne Journalière -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calculator text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Moyenne/Jour</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($avgDaily, 1) }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">{{ $stats[0]->unite ?? 'Go' }}/jour</div>
            </div>
        </div>

        <!-- Pic Journalier -->
        <div class="bg-gradient-to-br from-amber-500/10 to-orange-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bolt text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Pic/Jour</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($maxDaily, 1) }}</div>
                <div class="text-amber-400/70 text-sm mt-1">Max journalier</div>
            </div>
        </div>

        <!-- Types de données -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-layer-group text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">Catégories</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ count($types) }}</div>
                <div class="text-emerald-400/70 text-sm mt-1">Types de données</div>
            </div>
        </div>
    </div>

    <!-- 3D Stats Visualization -->
    <div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-cube text-purple-400"></i>
                Visualisation 3D de la consommation
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
            </div>
        </div>
        <div id="stats3D" class="h-80 w-full"></div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Chart Section -->
        <div class="lg:col-span-2 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-chart-area text-cyan-400"></i>
                    Évolution sur 30 jours
                </h3>
                <div class="flex gap-2">
                    <span class="text-xs text-slate-400 flex items-center gap-1">
                        <span class="w-3 h-3 rounded bg-purple-400"></span> Trafic
                    </span>
                    <span class="text-xs text-slate-400 flex items-center gap-1">
                        <span class="w-3 h-3 rounded bg-emerald-400"></span> Consommation
                    </span>
                </div>
            </div>
            <div class="p-4">
                <div class="relative h-72 bg-slate-900/50 rounded-xl overflow-hidden">
                    <canvas id="mainChart" class="w-full h-full"></canvas>
                    
                    @if(count($stats ?? []) === 0)
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-chart-line text-slate-600 text-2xl"></i>
                                </div>
                                <p class="text-slate-400">Aucune donnée de consommation</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Distribution by Type -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-chart-pie text-emerald-400"></i>
                    Répartition par type
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-48 flex items-center justify-center mb-4">
                    <canvas id="typeChart" class="max-h-full"></canvas>
                </div>
                
                <div class="space-y-2">
                    @forelse($types as $type => $items)
                        @php
                            $total = $items->sum('total');
                            $color = match($type) {
                                'trafic' => 'purple',
                                'consommation' => 'emerald',
                                default => 'slate'
                            };
                        @endphp
                        <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded bg-{{ $color }}-400"></span>
                                <span class="text-slate-300 text-sm capitalize">{{ $type }}</span>
                            </div>
                            <span class="text-{{ $color }}-400 font-medium">{{ number_format($total, 1) }}</span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-slate-400 text-sm">Aucune donnée</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-table text-amber-400"></i>
                Détails journaliers
            </h3>
            <span class="text-sm text-slate-400">{{ count($stats ?? []) }} entrées</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Date</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Type</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Valeur</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Progression</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @php
                        $maxValue = $stats->max('total') ?: 1;
                    @endphp
                    @forelse($stats ?? [] as $stat)
                    @php
                        $pct = min(100, ($stat->total / $maxValue) * 100);
                        $color = match($stat->type ?? '') {
                            'trafic' => 'purple',
                            'consommation' => 'emerald',
                            default => 'slate'
                        };
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-6 py-4 text-slate-300">{{ $stat->date }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-{{ $color }}-500/20 text-{{ $color }}-400 rounded-lg text-sm capitalize">{{ $stat->type ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-{{ $color }}-400 font-medium">{{ number_format($stat->total ?? 0, 1) }} {{ $stat->unite ?? '' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-32 bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div class="bg-{{ $color }}-400 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-inbox text-slate-600 text-2xl"></i>
                            </div>
                            <p class="text-slate-400">Aucune donnée de consommation disponible</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Three.js for 3D Stats Visualization -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    const container = document.getElementById('stats3D');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1e293b);
    scene.fog = new THREE.Fog(0x1e293b, 10, 40);
    
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(10, 8, 10);
    camera.lookAt(0, 0, 0);
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 1.5);
    scene.add(ambientLight);
    
    const pointLight = new THREE.PointLight(0xa855f7, 1.5, 50);
    pointLight.position.set(10, 15, 10);
    scene.add(pointLight);
    
    const pointLight2 = new THREE.PointLight(0x06b6d4, 1.5, 50);
    pointLight2.position.set(-10, 5, -10);
    scene.add(pointLight2);

    // Grid floor
    const gridHelper = new THREE.GridHelper(20, 20, 0x334155, 0x1e293b);
    scene.add(gridHelper);

    // Stats data from PHP
    const statsData = @json($stats ?? []);
    
    if (statsData.length > 0) {
        // Group by date and sum totals
        const dailyTotals = {};
        statsData.forEach(stat => {
            if (!dailyTotals[stat.date]) {
                dailyTotals[stat.date] = 0;
            }
            dailyTotals[stat.date] += stat.total;
        });
        
        const dates = Object.keys(dailyTotals).sort();
        const values = dates.map(d => dailyTotals[d]);
        const maxValue = Math.max(...values) || 1;
        const maxHeight = 8;
        
        // Create bars for each day (circular arrangement)
        const barCount = Math.min(dates.length, 15); // Max 15 bars
        const radius = 6;
        
        for (let i = 0; i < barCount; i++) {
            const dateIndex = Math.floor((dates.length / barCount) * i);
            const value = values[dateIndex] || 0;
            const height = Math.max(0.5, (value / maxValue) * maxHeight);
            
            // Color based on value (gradient from cyan to purple to rose)
            const ratio = value / maxValue;
            let color;
            if (ratio > 0.7) color = 0xf43f5e; // rose
            else if (ratio > 0.4) color = 0xa855f7; // purple
            else color = 0x06b6d4; // cyan
            
            // Create bar
            const geometry = new THREE.BoxGeometry(0.8, height, 0.8);
            const material = new THREE.MeshPhongMaterial({ 
                color: color,
                emissive: color,
                emissiveIntensity: 0.2,
                transparent: true,
                opacity: 0.9
            });
            const bar = new THREE.Mesh(geometry, material);
            
            // Position in circle
            const angle = (2 * Math.PI / barCount) * i;
            const x = radius * Math.cos(angle);
            const z = radius * Math.sin(angle);
            
            bar.position.set(x, height / 2, z);
            bar.userData = { originalY: height / 2, value: value, date: dates[dateIndex] };
            scene.add(bar);
            
            // Glow effect below bar
            const glowGeometry = new THREE.CircleGeometry(0.5, 16);
            const glowMaterial = new THREE.MeshBasicMaterial({ 
                color: color,
                transparent: true,
                opacity: 0.3,
                side: THREE.DoubleSide
            });
            const glow = new THREE.Mesh(glowGeometry, glowMaterial);
            glow.rotation.x = -Math.PI / 2;
            glow.position.set(x, 0.01, z);
            scene.add(glow);
        }
        
        // Center hub
        const hubGeometry = new THREE.CylinderGeometry(1, 1, 0.5, 16);
        const hubMaterial = new THREE.MeshPhongMaterial({ 
            color: 0xa855f7,
            emissive: 0x6b21a8,
            emissiveIntensity: 0.3
        });
        const hub = new THREE.Mesh(hubGeometry, hubMaterial);
        hub.position.y = 0.25;
        scene.add(hub);
        
        // Rotating ring around hub
        const ringGeometry = new THREE.TorusGeometry(2, 0.1, 8, 32);
        const ringMaterial = new THREE.MeshBasicMaterial({ 
            color: 0xa855f7,
            transparent: true,
            opacity: 0.4
        });
        const ring = new THREE.Mesh(ringGeometry, ringMaterial);
        ring.rotation.x = Math.PI / 2;
        ring.position.y = 0.5;
        scene.add(ring);
        
        // Floating particles
        const particles = [];
        for (let i = 0; i < 20; i++) {
            const geometry = new THREE.SphereGeometry(0.08, 8, 8);
            const material = new THREE.MeshBasicMaterial({ 
                color: 0xa855f7,
                transparent: true,
                opacity: 0.6
            });
            const particle = new THREE.Mesh(geometry, material);
            
            const angle = Math.random() * Math.PI * 2;
            const r = 3 + Math.random() * 4;
            particle.position.set(
                r * Math.cos(angle),
                1 + Math.random() * 5,
                r * Math.sin(angle)
            );
            particle.userData = {
                angle: angle,
                radius: r,
                speed: 0.01 + Math.random() * 0.02,
                yOffset: Math.random() * Math.PI * 2
            };
            scene.add(particle);
            particles.push(particle);
        }
        
        // Animation
        let isActive = true;
        const animate = () => {
            if (!isActive) return;
            requestAnimationFrame(animate);
            
            // Rotate ring
            ring.rotation.z += 0.01;
            
            // Animate particles
            particles.forEach(p => {
                p.userData.angle += p.userData.speed;
                p.position.x = p.userData.radius * Math.cos(p.userData.angle);
                p.position.z = p.userData.radius * Math.sin(p.userData.angle);
                p.position.y = 2 + Math.sin(Date.now() * 0.001 + p.userData.yOffset) * 2;
            });
            
            // Slow rotation of entire scene
            scene.rotation.y += 0.001;
            
            renderer.render(scene, camera);
        };
        
        animate();
    } else {
        // No data message
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 512;
        canvas.height = 64;
        ctx.fillStyle = 'transparent';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.font = '24px Arial';
        ctx.fillStyle = '#94a3b8';
        ctx.textAlign = 'center';
        ctx.fillText('Aucune donnée de consommation', 256, 40);
        
        const texture = new THREE.CanvasTexture(canvas);
        const geometry = new THREE.PlaneGeometry(8, 1);
        const material = new THREE.MeshBasicMaterial({ 
            map: texture, 
            transparent: true,
            side: THREE.DoubleSide
        });
        const mesh = new THREE.Mesh(geometry, material);
        mesh.position.set(0, 2, 0);
        scene.add(mesh);
        
        let isActive = true;
        const animate = () => {
            if (!isActive) return;
            requestAnimationFrame(animate);
            mesh.rotation.y += 0.005;
            renderer.render(scene, camera);
        };
        animate();
    }

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
    });
})();

// 2D Charts with Chart.js
const statsData = @json($stats ?? []);

// Main Line Chart
const ctxMain = document.getElementById('mainChart')?.getContext('2d');
if (ctxMain && statsData.length > 0) {
    // Group by date
    const dailyTotals = {};
    const dailyTypes = {};
    
    statsData.forEach(stat => {
        if (!dailyTotals[stat.date]) {
            dailyTotals[stat.date] = 0;
            dailyTypes[stat.date] = { trafic: 0, consommation: 0 };
        }
        dailyTotals[stat.date] += stat.total;
        if (stat.type === 'trafic') dailyTypes[stat.date].trafic += stat.total;
        else dailyTypes[stat.date].consommation += stat.total;
    });
    
    const dates = Object.keys(dailyTotals).sort();
    const traficData = dates.map(d => dailyTypes[d].trafic);
    const consoData = dates.map(d => dailyTypes[d].consommation);
    
    new Chart(ctxMain, {
        type: 'line',
        data: {
            labels: dates.map(d => d.substring(5)), // MM-DD
            datasets: [
                {
                    label: 'Trafic',
                    data: traficData,
                    borderColor: '#a855f7',
                    backgroundColor: 'rgba(168, 85, 247, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#a855f7'
                },
                {
                    label: 'Consommation',
                    data: consoData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#10b981'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94a3b8' }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#94a3b8' },
                    grid: { color: '#334155' }
                },
                y: {
                    ticks: { color: '#94a3b8' },
                    grid: { color: '#334155' },
                    beginAtZero: true
                }
            }
        }
    });
}

// Pie Chart for types
const ctxType = document.getElementById('typeChart')?.getContext('2d');
if (ctxType && statsData.length > 0) {
    const types = {};
    statsData.forEach(stat => {
        const type = stat.type || 'default';
        types[type] = (types[type] || 0) + stat.total;
    });
    
    const typeLabels = Object.keys(types).map(t => t.charAt(0).toUpperCase() + t.slice(1));
    const typeValues = Object.values(types);
    const typeColors = Object.keys(types).map(t => {
        switch(t) {
            case 'trafic': return '#a855f7';
            case 'consommation': return '#10b981';
            default: return '#64748b';
        }
    });
    
    new Chart(ctxType, {
        type: 'doughnut',
        data: {
            labels: typeLabels,
            datasets: [{
                data: typeValues,
                backgroundColor: typeColors,
                borderColor: '#1e293b',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Refresh function
function refreshData() {
    const btn = document.getElementById('refresh-icon');
    btn.classList.add('fa-spin');
    setTimeout(() => {
        window.location.reload();
    }, 500);
}
</script>
@endsection
