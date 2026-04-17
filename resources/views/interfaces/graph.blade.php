@extends('layouts.app')

@section('title', 'Graphiques - ' . $interface->nom)

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('interfaces.index') }}" class="p-3 bg-slate-800 hover:bg-slate-700 rounded-xl transition flex items-center justify-center">
                <i class="fas fa-arrow-left text-cyan-400"></i>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
                        <i class="fas fa-chart-line mr-3"></i>{{ $interface->nom }}
                    </h1>
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $interface->statut == 'actif' ? 'bg-emerald-500/20 text-emerald-400' : ($interface->statut == 'erreur' ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400') }}">
                        <span class="w-2 h-2 rounded-full bg-current inline-block mr-1.5 animate-pulse"></span>
                        {{ ucfirst($interface->statut) }}
                    </span>
                </div>
                <p class="text-slate-400 flex items-center gap-4 text-sm">
                    <span><i class="fas fa-server mr-2 text-cyan-400/70"></i>{{ $interface->routeur->nom ?? 'N/A' }}</span>
                    <span><i class="fas fa-network-wired mr-2 text-cyan-400/70"></i>{{ $interface->routeur->adresse_ip ?? 'N/A' }}</span>
                    @if($interface->adresse_mac)
                        <span><i class="fas fa-fingerprint mr-2 text-cyan-400/70"></i>{{ $interface->adresse_mac }}</span>
                    @endif
                </p>
            </div>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('interfaces.show', $interface) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="fas fa-info-circle text-cyan-400"></i>
                <span>Détails</span>
            </a>
            <button onclick="refreshData()" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center gap-2 shadow-lg shadow-cyan-500/25">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
                <span>Actualiser</span>
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Debit entrant -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-cyan-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-download text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Entrant (Rx)</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($interface->debit_entrant, 1) }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">Mbps</div>
            </div>
        </div>

        <!-- Debit sortant -->
        <div class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-emerald-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-emerald-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-upload text-emerald-400 text-xl"></i>
                    </div>
                    <span class="text-emerald-400 text-sm font-medium">Sortant (Tx)</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($interface->debit_sortant, 1) }}</div>
                <div class="text-emerald-400/70 text-sm mt-1">Mbps</div>
            </div>
        </div>

        <!-- Total traffic -->
        <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exchange-alt text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Total</span>
                </div>
                <div class="text-3xl font-bold text-white">{{ number_format($interface->debit_entrant + $interface->debit_sortant, 1) }}</div>
                <div class="text-amber-400/70 text-sm mt-1">Mbps</div>
            </div>
        </div>

        <!-- Utilisation -->
        <div class="bg-gradient-to-br from-purple-500/10 to-purple-600/5 border border-purple-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-purple-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-purple-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tachometer-alt text-purple-400 text-xl"></i>
                    </div>
                    <span class="text-purple-400 text-sm font-medium">Utilisation</span>
                </div>
                @php
                    $capacity = 1000; // Assume 1Gbps capacity
                    $utilization = min(100, (($interface->debit_entrant + $interface->debit_sortant) / $capacity) * 100);
                @endphp
                <div class="text-3xl font-bold text-white">{{ number_format($utilization, 1) }}%</div>
                <div class="w-full bg-slate-700 rounded-full h-2 mt-2 overflow-hidden">
                    <div class="bg-gradient-to-r from-cyan-400 to-emerald-400 h-2 rounded-full transition-all duration-1000" style="width: {{ $utilization }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3D Traffic Visualization -->
    <div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-cube text-cyan-400"></i>
                Visualisation 3D du trafic
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-cyan-400"></span> Entrant
                </span>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-3 h-3 rounded bg-emerald-400"></span> Sortant
                </span>
                <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
            </div>
        </div>
        <div id="traffic3D" class="h-80 w-full"></div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Traffic Chart -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-chart-area text-cyan-400"></i>
                    Trafic sur 24h
                </h3>
                <select class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-sm text-slate-300 focus:outline-none">
                    <option>Dernières 24h</option>
                    <option>7 jours</option>
                    <option>30 jours</option>
                </select>
            </div>
            <div class="p-4">
                <div class="relative h-64 bg-slate-900/50 rounded-xl overflow-hidden">
                    <!-- Canvas for real-time chart -->
                    <canvas id="trafficChart" class="w-full h-full"></canvas>
                    
                    @if(count($historique) === 0)
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-chart-line text-slate-600 text-2xl"></i>
                                </div>
                                <p class="text-slate-400">Aucune donnée historique</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Packet Analysis -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-circle-notch text-emerald-400"></i>
                    Répartition du trafic
                </h3>
            </div>
            <div class="p-4">
                <div class="relative h-64 flex items-center justify-center">
                    <canvas id="pieChart" class="max-h-full"></canvas>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-cyan-400"></span>
                            <span class="text-slate-300 text-sm">Entrant</span>
                        </div>
                        <span class="text-cyan-400 font-medium">{{ $interface->debit_entrant > 0 ? round(($interface->debit_entrant / ($interface->debit_entrant + $interface->debit_sortant)) * 100, 1) : 0 }}%</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-lg">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded bg-emerald-400"></span>
                            <span class="text-slate-300 text-sm">Sortant</span>
                        </div>
                        <span class="text-emerald-400 font-medium">{{ $interface->debit_sortant > 0 ? round(($interface->debit_sortant / ($interface->debit_entrant + $interface->debit_sortant)) * 100, 1) : 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-table text-amber-400"></i>
                Historique détaillé
            </h3>
            <span class="text-sm text-slate-400">{{ count($historique) }} entrées</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Heure</th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded bg-cyan-400"></span>
                                Débit entrant
                            </span>
                        </th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded bg-emerald-400"></span>
                                Débit sortant
                            </span>
                        </th>
                        <th class="text-left px-6 py-4 text-sm font-medium text-slate-400">Total</th>
                    </tr>
                </thead>
                @php
                    $maxRx = $historique->max('debit_entrant') ?: 1;
                    $maxTx = $historique->max('debit_sortant') ?: 1;
                @endphp
                <tbody class="divide-y divide-slate-700/50">
                    @forelse($historique as $row)
                    @php
                        $rxPct = min(100, ($row['debit_entrant'] / $maxRx) * 100);
                        $txPct = min(100, ($row['debit_sortant'] / $maxTx) * 100);
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-6 py-4 text-slate-300 font-mono text-sm">{{ $row['heure'] }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-cyan-400 font-medium">{{ $row['debit_entrant'] }} Mbps</span>
                                <div class="w-20 bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-cyan-400 h-1.5 rounded-full transition-all" style="width: {{ $rxPct }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-emerald-400 font-medium">{{ $row['debit_sortant'] }} Mbps</span>
                                <div class="w-20 bg-slate-700 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-400 h-1.5 rounded-full transition-all" style="width: {{ $txPct }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white font-medium">{{ number_format($row['debit_entrant'] + $row['debit_sortant'], 1) }} Mbps</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-inbox text-slate-600 text-2xl"></i>
                            </div>
                            <p class="text-slate-400">Aucune donnée historique disponible</p>
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
    scene.fog = new THREE.Fog(0x1e293b, 10, 30);
    
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(8, 5, 8);
    camera.lookAt(0, 0, 0);
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 1.5);
    scene.add(ambientLight);
    
    const pointLight = new THREE.PointLight(0x00d4ff, 2, 50);
    pointLight.position.set(10, 10, 10);
    scene.add(pointLight);
    
    const pointLight2 = new THREE.PointLight(0x00ff88, 1.5, 50);
    pointLight2.position.set(-10, -5, 10);
    scene.add(pointLight2);

    // Grid floor
    const gridHelper = new THREE.GridHelper(20, 20, 0x334155, 0x1e293b);
    scene.add(gridHelper);

    // Data
    const rxData = {{ $interface->debit_entrant }};
    const txData = {{ $interface->debit_sortant }};
    const totalData = rxData + txData;

    // Central Interface Node
    const centerGeometry = new THREE.CylinderGeometry(0.5, 0.5, 2, 8);
    const centerMaterial = new THREE.MeshPhongMaterial({ 
        color: 0x00d4ff,
        emissive: 0x0066aa,
        emissiveIntensity: 0.3,
        shininess: 100
    });
    const centerNode = new THREE.Mesh(centerGeometry, centerMaterial);
    centerNode.rotation.z = Math.PI / 4;
    centerNode.rotation.y = Math.PI / 6;
    scene.add(centerNode);

    // Glow ring
    const ringGeometry = new THREE.TorusGeometry(2, 0.1, 8, 32);
    const ringMaterial = new THREE.MeshBasicMaterial({ 
        color: 0x00d4ff,
        transparent: true,
        opacity: 0.5
    });
    const ring = new THREE.Mesh(ringGeometry, ringMaterial);
    ring.rotation.x = Math.PI / 2;
    scene.add(ring);

    // Incoming (Rx) Tower
    const rxHeight = Math.max(1, rxData / 10);
    const rxGeometry = new THREE.BoxGeometry(1, rxHeight, 1);
    const rxMaterial = new THREE.MeshPhongMaterial({ 
        color: 0x06b6d4,
        emissive: 0x06b6d4,
        emissiveIntensity: 0.2,
        transparent: true,
        opacity: 0.9
    });
    const rxTower = new THREE.Mesh(rxGeometry, rxMaterial);
    rxTower.position.set(-3, rxHeight / 2, 0);
    scene.add(rxTower);

    // Incoming particles
    const rxParticles = [];
    for (let i = 0; i < 8; i++) {
        const geometry = new THREE.SphereGeometry(0.1, 8, 8);
        const material = new THREE.MeshBasicMaterial({ color: 0x06b6d4 });
        const particle = new THREE.Mesh(geometry, material);
        particle.userData = {
            angle: (2 * Math.PI / 8) * i,
            radius: 4,
            speed: 0.02 + Math.random() * 0.02,
            height: Math.random() * rxHeight
        };
        scene.add(particle);
        rxParticles.push(particle);
    }

    // Outgoing (Tx) Tower
    const txHeight = Math.max(1, txData / 10);
    const txGeometry = new THREE.BoxGeometry(1, txHeight, 1);
    const txMaterial = new THREE.MeshPhongMaterial({ 
        color: 0x10b981,
        emissive: 0x10b981,
        emissiveIntensity: 0.2,
        transparent: true,
        opacity: 0.9
    });
    const txTower = new THREE.Mesh(txGeometry, txMaterial);
    txTower.position.set(3, txHeight / 2, 0);
    scene.add(txTower);

    // Outgoing particles
    const txParticles = [];
    for (let i = 0; i < 8; i++) {
        const geometry = new THREE.SphereGeometry(0.1, 8, 8);
        const material = new THREE.MeshBasicMaterial({ color: 0x10b981 });
        const particle = new THREE.Mesh(geometry, material);
        particle.userData = {
            angle: (2 * Math.PI / 8) * i,
            radius: 4,
            speed: 0.02 + Math.random() * 0.02,
            height: Math.random() * txHeight
        };
        scene.add(particle);
        txParticles.push(particle);
    }

    // Labels (using simple text geometry with planes)
    const labelRx = createLabel('↓ IN', 0x06b6d4);
    labelRx.position.set(-3, rxHeight + 1, 0);
    scene.add(labelRx);

    const labelTx = createLabel('↑ OUT', 0x10b981);
    labelTx.position.set(3, txHeight + 1, 0);
    scene.add(labelTx);

    function createLabel(text, color) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 128;
        canvas.height = 64;
        ctx.fillStyle = 'transparent';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.font = 'bold 24px Arial';
        ctx.fillStyle = '#' + color.toString(16).padStart(6, '0');
        ctx.textAlign = 'center';
        ctx.fillText(text, 64, 40);
        
        const texture = new THREE.CanvasTexture(canvas);
        const geometry = new THREE.PlaneGeometry(2, 1);
        const material = new THREE.MeshBasicMaterial({ 
            map: texture, 
            transparent: true,
            side: THREE.DoubleSide
        });
        return new THREE.Mesh(geometry, material);
    }

    // Animation
    let isActive = true;
    const animate = () => {
        if (!isActive) return;
        requestAnimationFrame(animate);

        // Rotate center node
        centerNode.rotation.y += 0.01;
        ring.rotation.z += 0.005;

        // Animate Rx particles (inward spiral)
        rxParticles.forEach(p => {
            p.userData.angle += p.userData.speed;
            p.position.x = -3 + Math.cos(p.userData.angle) * p.userData.radius * 0.5;
            p.position.z = Math.sin(p.userData.angle) * p.userData.radius * 0.5;
            p.position.y = p.userData.height + Math.sin(Date.now() * 0.003) * 0.2;
        });

        // Animate Tx particles (outward spiral)
        txParticles.forEach(p => {
            p.userData.angle -= p.userData.speed;
            p.position.x = 3 + Math.cos(p.userData.angle) * p.userData.radius * 0.5;
            p.position.z = Math.sin(p.userData.angle) * p.userData.radius * 0.5;
            p.position.y = p.userData.height + Math.sin(Date.now() * 0.003 + 1) * 0.2;
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

// 2D Charts with Chart.js
const historiqueData = @json($historique);
const labels = historiqueData.map(h => h.heure);
const rxData = historiqueData.map(h => h.debit_entrant);
const txData = historiqueData.map(h => h.debit_sortant);

// Traffic Chart
const ctxTraffic = document.getElementById('trafficChart')?.getContext('2d');
if (ctxTraffic && historiqueData.length > 0) {
    new Chart(ctxTraffic, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Entrant (Rx)',
                    data: rxData,
                    borderColor: '#06b6d4',
                    backgroundColor: 'rgba(6, 182, 212, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#06b6d4'
                },
                {
                    label: 'Sortant (Tx)',
                    data: txData,
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
                    title: { display: true, text: 'Mbps', color: '#94a3b8' }
                }
            }
        }
    });
}

// Pie Chart
const ctxPie = document.getElementById('pieChart')?.getContext('2d');
const rx = {{ $interface->debit_entrant }};
const tx = {{ $interface->debit_sortant }};
if (ctxPie && (rx > 0 || tx > 0)) {
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Entrant', 'Sortant'],
            datasets: [{
                data: [rx, tx],
                backgroundColor: ['#06b6d4', '#10b981'],
                borderColor: '#1e293b',
                borderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        color: '#94a3b8',
                        padding: 20
                    }
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
