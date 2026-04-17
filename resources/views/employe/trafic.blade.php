@extends('layouts.app')

@section('title', 'Mon Trafic')

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-purple-500 bg-clip-text text-transparent">
                <i class="fas fa-chart-area mr-3"></i>Mon Trafic
            </h1>
            <p class="text-slate-400 mt-1">Suivi de votre consommation sur 30 derniers jours</p>
        </div>
        
        <div class="flex gap-3">
            <button onclick="refreshData()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
                <span>Actualiser</span>
            </button>
            <a href="{{ route('employe.dashboard') }}" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center gap-2 shadow-lg shadow-cyan-500/25">
                <i class="fas fa-arrow-left"></i>
                <span>Retour</span>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Total Mois -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-download text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Total Mois</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($totalMois ?? 0, 0) }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">{{ $traficJournalier[0]->unite ?? 'Go' }}</div>
            </div>
        </div>

        <!-- Jours enregistrés -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-check text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">Jours</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ count($traficJournalier ?? []) }}</div>
                <div class="text-emerald-400/70 text-sm mt-1">sur 30 jours</div>
            </div>
        </div>

        <!-- Moyenne journalière -->
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calculator text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Moyenne/Jour</span>
                </div>
                @php
                    $moyenne = count($traficJournalier ?? []) > 0 ? ($totalMois / count($traficJournalier)) : 0;
                @endphp
                <div class="text-3xl font-bold text-white">{{ number_format($moyenne, 1) }}</div>
                <div class="text-amber-400/70 text-sm mt-1">{{ $traficJournalier[0]->unite ?? 'Go' }}/jour</div>
            </div>
        </div>

        <!-- Pic de consommation -->
        <div class="bg-gradient-to-br from-rose-500/10 to-rose-600/5 border border-rose-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-rose-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-rose-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-rose-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-rose-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bolt text-rose-400 text-xl"></i>
                    </div>
                    <span class="text-rose-400 text-sm font-medium">Pic/Jour</span>
                </div>
                @php
                    $pic = collect($traficJournalier ?? [])->max('total') ?? 0;
                @endphp
                <div class="text-3xl font-bold text-white">{{ number_format($pic, 1) }}</div>
                <div class="text-rose-400/70 text-sm mt-1">Max journalier</div>
            </div>
        </div>
    </div>

    <!-- 3D Traffic Visualization -->
    <div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-cube text-cyan-400"></i>
                Visualisation 3D de la consommation
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-cyan-400"></span> Faible
                </span>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-emerald-400"></span> Moyenne
                </span>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-rose-400"></span> Élevée
                </span>
                <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
            </div>
        </div>
        <div id="traffic3D" class="h-80 w-full"></div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        <!-- Chart Section -->
        <div class="xl:col-span-2 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-chart-line text-cyan-400"></i>
                    Évolution journalière (30 jours)
                </h3>
                <div class="flex gap-2">
                    <span class="px-3 py-1 bg-cyan-500/20 text-cyan-400 rounded-lg text-sm">30j</span>
                </div>
            </div>
            <div class="p-4">
                <div class="relative h-72 bg-slate-900/50 rounded-xl overflow-hidden">
                    <canvas id="trafficChart" class="w-full h-full"></canvas>
                    
                    @if(count($traficJournalier ?? []) === 0)
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-chart-line text-slate-600 text-2xl"></i>
                                </div>
                                <p class="text-slate-400">Aucune donnée de trafic disponible</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Distribution -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-chart-pie text-emerald-400"></i>
                    Répartition par type
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-48 flex items-center justify-center mb-4">
                    <canvas id="pieChart" class="max-h-full"></canvas>
                </div>
                
                @php
                    $types = collect($traficJournalier ?? [])->groupBy('type');
                @endphp
                
                <div class="space-y-2">
                    @forelse($types as $type => $items)
                        @php
                            $total = $items->sum('total');
                            $color = match($type) {
                                'trafic' => 'cyan',
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
                Détails par jour
            </h3>
            <span class="text-sm text-slate-400">{{ count($traficJournalier ?? []) }} entrées</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Date</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Type</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded bg-cyan-400"></span>
                                Valeur
                            </span>
                        </th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Unité</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Progression</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @php
                        $maxValue = collect($traficJournalier ?? [])->max('total') ?: 1;
                    @endphp
                    @forelse($traficJournalier ?? [] as $trafic)
                    @php
                        $pct = min(100, ($trafic->total / $maxValue) * 100);
                        $color = match($trafic->type ?? '') {
                            'trafic' => 'cyan',
                            'consommation' => 'emerald',
                            default => 'slate'
                        };
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-6 py-4 text-slate-300">{{ $trafic->date }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-{{ $color }}-500/20 text-{{ $color }}-400 rounded-lg text-sm capitalize">{{ $trafic->type ?? '—' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-{{ $color }}-400 font-medium">{{ number_format($trafic->total ?? 0, 1) }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-400">{{ $trafic->unite ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <div class="w-24 bg-slate-700 rounded-full h-2 overflow-hidden">
                                <div class="bg-{{ $color }}-400 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-inbox text-slate-600 text-2xl"></i>
                            </div>
                            <p class="text-slate-400">Aucune donnée de trafic disponible</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Three.js for 3D Traffic Visualization -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    const container = document.getElementById('traffic3D');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1e293b);
    scene.fog = new THREE.Fog(0x1e293b, 10, 40);
    
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(12, 8, 12);
    camera.lookAt(0, 0, 0);
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 1.5);
    scene.add(ambientLight);
    
    const pointLight = new THREE.PointLight(0x00d4ff, 1.5, 50);
    pointLight.position.set(10, 15, 10);
    scene.add(pointLight);
    
    const pointLight2 = new THREE.PointLight(0x10b981, 1.5, 50);
    pointLight2.position.set(-10, 5, -10);
    scene.add(pointLight2);

    // Grid floor
    const gridHelper = new THREE.GridHelper(20, 20, 0x334155, 0x1e293b);
    scene.add(gridHelper);

    // Traffic data from PHP
    const trafficData = @json($traficJournalier ?? []);
    
    if (trafficData.length > 0) {
        const maxValue = Math.max(...trafficData.map(t => t.total)) || 1;
        const maxHeight = 8;
        
        // Create bars for each data point (circular arrangement)
        const barCount = Math.min(trafficData.length, 20); // Max 20 bars
        const radius = 6;
        
        for (let i = 0; i < barCount; i++) {
            const data = trafficData[i];
            const height = Math.max(0.5, (data.total / maxValue) * maxHeight);
            
            // Color based on value
            let color = 0x06b6d4; // cyan
            const ratio = data.total / maxValue;
            if (ratio > 0.7) color = 0xf43f5e; // rose (high)
            else if (ratio > 0.4) color = 0x10b981; // emerald (medium)
            
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
            bar.userData = { originalY: height / 2, value: data.total, date: data.date };
            scene.add(bar);
            
            // Glow effect below bar
            const glowGeometry = new THREE.CircleGeometry(0.6, 16);
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
            
            // Connection line to center
            const lineGeometry = new THREE.BufferGeometry().setFromPoints([
                new THREE.Vector3(x, 0, z),
                new THREE.Vector3(0, 0, 0)
            ]);
            const lineMaterial = new THREE.LineBasicMaterial({ 
                color: color,
                transparent: true,
                opacity: 0.15
            });
            const line = new THREE.Line(lineGeometry, lineMaterial);
            scene.add(line);
        }
        
        // Center hub
        const hubGeometry = new THREE.CylinderGeometry(1, 1, 0.5, 16);
        const hubMaterial = new THREE.MeshPhongMaterial({ 
            color: 0x00d4ff,
            emissive: 0x0066aa,
            emissiveIntensity: 0.3
        });
        const hub = new THREE.Mesh(hubGeometry, hubMaterial);
        hub.position.y = 0.25;
        scene.add(hub);
        
        // Rotating ring around hub
        const ringGeometry = new THREE.TorusGeometry(2, 0.1, 8, 32);
        const ringMaterial = new THREE.MeshBasicMaterial({ 
            color: 0x00d4ff,
            transparent: true,
            opacity: 0.4
        });
        const ring = new THREE.Mesh(ringGeometry, ringMaterial);
        ring.rotation.x = Math.PI / 2;
        ring.position.y = 0.5;
        scene.add(ring);
        
        // Floating particles
        const particles = [];
        for (let i = 0; i < 15; i++) {
            const geometry = new THREE.SphereGeometry(0.1, 8, 8);
            const material = new THREE.MeshBasicMaterial({ 
                color: 0x00d4ff,
                transparent: true,
                opacity: 0.6
            });
            const particle = new THREE.Mesh(geometry, material);
            
            const angle = Math.random() * Math.PI * 2;
            const r = 3 + Math.random() * 4;
            particle.position.set(
                r * Math.cos(angle),
                1 + Math.random() * 4,
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
                p.position.y = 2 + Math.sin(Date.now() * 0.001 + p.userData.yOffset) * 1.5;
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
        ctx.fillText('Aucune donnée de trafic', 256, 40);
        
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
        if (isActive && trafficData.length > 0) animate();
    });
})();

// 2D Charts with Chart.js
const trafficData = @json($traficJournalier ?? []);

// Traffic Line Chart
const ctxTraffic = document.getElementById('trafficChart')?.getContext('2d');
if (ctxTraffic && trafficData.length > 0) {
    const labels = trafficData.map(t => t.date);
    const values = trafficData.map(t => t.total);
    
    // Generate gradient colors based on values
    const maxValue = Math.max(...values);
    const colors = values.map(v => {
        const ratio = v / maxValue;
        if (ratio > 0.7) return '#f43f5e'; // rose
        if (ratio > 0.4) return '#10b981'; // emerald
        return '#06b6d4'; // cyan
    });
    
    new Chart(ctxTraffic, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Consommation',
                data: values,
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6, 182, 212, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: colors,
                pointBorderColor: '#1e293b',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#fff',
                    bodyColor: '#94a3b8',
                    borderColor: '#334155',
                    borderWidth: 1,
                    padding: 10,
                    callbacks: {
                        label: (context) => `Consommation: ${context.parsed.y.toFixed(1)} ${trafficData[0]?.unite || 'Go'}`
                    }
                }
            },
            scales: {
                x: {
                    ticks: { 
                        color: '#94a3b8',
                        maxRotation: 45,
                        minRotation: 45
                    },
                    grid: { color: '#334155', drawBorder: false }
                },
                y: {
                    ticks: { color: '#94a3b8' },
                    grid: { color: '#334155', drawBorder: false },
                    beginAtZero: true
                }
            }
        }
    });
}

// Pie Chart for types
const ctxPie = document.getElementById('pieChart')?.getContext('2d');
if (ctxPie && trafficData.length > 0) {
    const types = {};
    trafficData.forEach(t => {
        const type = t.type || 'default';
        types[type] = (types[type] || 0) + t.total;
    });
    
    const typeLabels = Object.keys(types).map(t => t.charAt(0).toUpperCase() + t.slice(1));
    const typeValues = Object.values(types);
    const typeColors = Object.keys(types).map(t => {
        switch(t) {
            case 'trafic': return '#06b6d4';
            case 'consommation': return '#10b981';
            default: return '#64748b';
        }
    });
    
    new Chart(ctxPie, {
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
