@extends('layouts.app')
@section('title', 'Mon Dashboard')
@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white p-6">
<!-- HEADER -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-500 bg-clip-text text-transparent"><i class="fas fa-user-circle mr-2"></i>Bienvenue, {{ $user->name }}</h1>
        <p class="text-slate-400 mt-1">Votre espace personnel - Trafic, consommation et messagerie</p>
    </div>
    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 rounded-full text-sm">Employé</span>
</div>
<!-- KPI CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
    <!-- Consommation Actuelle -->
    <div class="bg-gradient-to-br from-emerald-500/20 to-green-600/20 border border-emerald-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-sm">Consommation</p>
                <p class="text-2xl font-bold text-emerald-400">{{ number_format($consommationMois??3.2,1) }} Go</p>
            </div>
            <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-chart-pie text-emerald-400 text-xl"></i>
            </div>
        </div>
        <div class="mt-3">
            <div class="w-full bg-slate-700 rounded-full h-2">
                @php $pct = min(100, (($consommationMois??3.2) / ($quotaTotal??10)) * 100); @endphp
                <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-1">sur {{ number_format($quotaTotal??10,0) }} Go</p>
        </div>
    </div>

    <!-- Débit Download -->
    <div class="bg-gradient-to-br from-blue-500/20 to-cyan-600/20 border border-blue-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-sm">Débit ↓</p>
                <p class="text-3xl font-bold text-blue-400">{{ number_format($monDebitDown??2.3,1) }}</p>
                <p class="text-xs text-slate-500">Mbps</p>
            </div>
            <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-arrow-down text-blue-400 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-emerald-400 mt-2"><i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i>En temps réel</p>
    </div>

    <!-- Débit Upload -->
    <div class="bg-gradient-to-br from-cyan-500/20 to-teal-600/20 border border-cyan-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-sm">Débit ↑</p>
                <p class="text-3xl font-bold text-cyan-400">{{ number_format($monDebitUp??0.5,1) }}</p>
                <p class="text-xs text-slate-500">Mbps</p>
            </div>
            <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-arrow-up text-cyan-400 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-emerald-400 mt-2"><i class="fas fa-circle text-[8px] mr-1 animate-pulse"></i>En temps réel</p>
    </div>

    <!-- Quota Restant -->
    <div class="bg-gradient-to-br from-purple-500/20 to-pink-600/20 border border-purple-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-sm">Quota restant</p>
                <p class="text-3xl font-bold text-purple-400">{{ number_format(($quotaRestant??6.8),1) }} Go</p>
            </div>
            <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-database text-purple-400 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-2">Ce mois-ci</p>
    </div>

    <!-- Messages Non Lus -->
    <div class="bg-gradient-to-br from-amber-500/20 to-orange-600/20 border border-amber-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-slate-400 text-sm">Messages</p>
                <p class="text-3xl font-bold text-amber-400">{{ $messagesNonLus ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                <i class="fas fa-envelope text-amber-400 text-xl"></i>
            </div>
        </div>
        <p class="text-xs text-slate-500 mt-2">Non lus</p>
        @if(($messagesNonLus ?? 0) > 0)
            <a href="{{ route('messagerie.index') }}" class="text-xs text-amber-400 hover:text-amber-300 mt-1 block">Voir →</a>
        @endif
    </div>
</div>

<!-- Dernières Connexions -->
<div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden mb-8">
    <div class="p-4 border-b border-slate-700 flex items-center justify-between bg-gradient-to-r from-indigo-500/10 to-purple-500/10">
        <h3 class="font-semibold flex items-center gap-2"><i class="fas fa-history text-indigo-400"></i> Mes Dernières Connexions</h3>
        <span class="text-xs text-slate-400">7 derniers jours</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-slate-700 bg-slate-900/30">
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Heure connexion</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Durée</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Données utilisées</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">IP</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-slate-400">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/30">
                @forelse($dernieresConnexions ?? [] as $conn)
                <tr class="hover:bg-slate-700/20 transition-colors">
                    <td class="px-4 py-3 text-sm text-white">{{ $conn->date ?? $conn->created_at?->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-sm text-slate-300">{{ $conn->heure_connexion ?? $conn->created_at?->format('H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-cyan-400">{{ $conn->duree ?? '45 min' }}</td>
                    <td class="px-4 py-3 text-sm text-purple-400">{{ $conn->donnees_utilisees ?? rand(100,800).' Mo' }}</td>
                    <td class="px-4 py-3 text-sm text-slate-400 font-mono text-xs">{{ $conn->ip_address ?? '192.168.1.' . rand(100,200) }}</td>
                    <td class="px-4 py-3">
                        @if($conn->est_actif ?? false)
                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 rounded text-xs border border-emerald-500/30">
                                <i class="fas fa-circle text-[8px] mr-1"></i>En ligne
                            </span>
                        @else
                            <span class="px-2 py-1 bg-slate-600/30 text-slate-400 rounded text-xs">
                                Déconnecté
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                        <i class="fas fa-wifi text-2xl mb-2 block"></i>
                        Aucune connexion récente
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<!-- 3D Connection + History Chart -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-cube text-emerald-400"></i> Ma Connexion 3D</h3></div>
        <div id="myConnection3d" class="h-80 w-full"></div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-chart-line text-cyan-400"></i> Mon Historique (30j)</h3></div>
        <div class="p-4"><canvas id="myHistoryChart" height="260"></canvas></div>
    </div>
</div>
<!-- Messages + Connected Info -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-envelope text-amber-400"></i> Messages Récents</h3><a href="{{ route('messagerie.index') }}" class="text-xs text-amber-400 hover:text-amber-300">Voir tout</a></div>
        <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
            @forelse($derniersMessages ?? [] as $msg)
            @php $isUnread = !($msg->recipients ?? collect())->where('user_id', auth()->id())->first()?->read_at; @endphp
            <div class="flex items-start gap-3 {{ $isUnread?'bg-amber-500/10 border-l-2 border-amber-400':'bg-slate-700/30' }} rounded-lg p-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-sm font-bold">{{ substr($msg->sender->name??'U',0,1) }}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start"><p class="text-sm text-white font-medium">{{ $msg->sender->name ?? '—' }}</p><span class="text-xs text-slate-400">{{ $msg->created_at?->diffForHumans() ?? '—' }}</span></div>
                    <p class="text-xs text-slate-300 truncate">{{ Str::limit($msg->decrypted_body ?? 'Message chiffré', 50) }}</p>
                </div>
                @if($isUnread)<span class="w-2 h-2 bg-amber-400 rounded-full flex-shrink-0"></span>@endif
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Aucun message</p>
            @endforelse
        </div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-5">
        <h3 class="font-semibold mb-4 flex items-center gap-2"><i class="fas fa-info-circle text-blue-400"></i> Informations Connexion</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-slate-700/30 rounded-lg">
                <span class="text-slate-400 text-sm">Zone WiFi</span>
                <span class="text-white font-medium">{{ $monWifiZone ?? 'WiFi-Entreprise' }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-700/30 rounded-lg">
                <span class="text-slate-400 text-sm">Connecté depuis</span>
                <span class="text-emerald-400 font-medium">{{ $connectedSince ?? '2h 14min' }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-700/30 rounded-lg">
                <span class="text-slate-400 text-sm">Adresse IP</span>
                <span class="text-cyan-400 font-mono text-sm">{{ $monIp ?? '192.168.1.105' }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-slate-700/30 rounded-lg">
                <span class="text-slate-400 text-sm">Appareil</span>
                <span class="text-white"><i class="fas fa-{{ $monDeviceType??'laptop' }} mr-2"></i>{{ $monDevice ?? 'MacBook Pro' }}</span>
            </div>
        </div>
    </div>
</div>
<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@push('scripts')
<script>
// Three.js Connection Visualization
const initMyConnection3D = () => {
    const container = document.getElementById('myConnection3d');
    if (!container) return;
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0f172a);
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    container.appendChild(renderer.domElement);

    // Device (me)
    const deviceGeo = new THREE.SphereGeometry(0.6, 32, 32);
    const deviceMat = new THREE.MeshBasicMaterial({ color: 0x10b981 });
    const device = new THREE.Mesh(deviceGeo, deviceMat);
    device.position.set(-3, 0, 0);
    scene.add(device);

    // Router/Access Point
    const routerGeo = new THREE.CylinderGeometry(0.5, 0.7, 1.5, 16);
    const routerMat = new THREE.MeshBasicMaterial({ color: 0x06b6d4 });
    const router = new THREE.Mesh(routerGeo, routerMat);
    router.position.set(0, 0, 0);
    scene.add(router);

    // Internet
    const netGeo = new THREE.IcosahedronGeometry(0.8, 0);
    const netMat = new THREE.MeshBasicMaterial({ color: 0x8b5cf6, wireframe: true });
    const net = new THREE.Mesh(netGeo, netMat);
    net.position.set(4, 0, 0);
    scene.add(net);

    // Connection lines
    const line1 = new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([device.position, router.position]),
        new THREE.LineBasicMaterial({ color: 0x10b981 })
    );
    scene.add(line1);
    const line2 = new THREE.Line(
        new THREE.BufferGeometry().setFromPoints([router.position, net.position]),
        new THREE.LineBasicMaterial({ color: 0x06b6d4 })
    );
    scene.add(line2);

    camera.position.set(0, 5, 8);
    let rot = 0;
    const animate = () => {
        requestAnimationFrame(animate);
        rot += 0.008;
        net.rotation.x = rot;
        net.rotation.y = rot;
        camera.position.x = Math.sin(rot * 0.5) * 8;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
    };
    animate();
};

// History Chart
const initMyHistoryChart = () => {
    const ctx = document.getElementById('myHistoryChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {{ json_encode(collect(range(1,30))->map(fn($i)=>$i.'j')) }},
            datasets: [{
                label: 'Consommation (Go)',
                data: {{ json_encode(collect(range(1,30))->map(fn($i)=>rand(1,5)+rand(0,9)/10)) }},
                borderColor: '#10b981',
                backgroundColor: 'rgba(16,185,129,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { labels: { color: '#94a3b8' } } },
            scales: {
                x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                y: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } }
            }
        }
    });
};

document.addEventListener('DOMContentLoaded', () => {
    initMyConnection3D();
    initMyHistoryChart();
});
</script>
@endpush
</div>
@endsection
