@extends('layouts.app')
@section('title', 'Admin Réseau')
@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white p-6">
<!-- HEADER -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent"><i class="fas fa-server mr-2"></i>Admin Réseau</h1>
        <p class="text-slate-400 mt-1">Infrastructure réseau en temps réel</p>
    </div>
    <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-sm flex items-center gap-1"><span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span> Actif</span>
</div>
<!-- KPI -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Routeurs actifs</p><p class="text-3xl font-bold text-cyan-400">{{ $routeursEnLigne ?? 0 }}</p></div><div class="w-14 h-14 bg-cyan-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-server text-cyan-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">{{ $routeursHorsLigne ?? 0 }} hors ligne</p>
    </div>
    <div class="bg-gradient-to-br from-purple-500/20 to-pink-600/20 border border-purple-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Zones WiFi</p><p class="text-3xl font-bold text-purple-400">{{ $zonesWifi ?? 0 }}</p></div><div class="w-14 h-14 bg-purple-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-wifi text-purple-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">configurées</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-500/20 to-green-600/20 border border-emerald-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Utilisateurs</p><p class="text-3xl font-bold text-emerald-400">{{ $utilisateursConnectes ?? $clientsWiFi }}</p></div><div class="w-14 h-14 bg-emerald-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-users text-emerald-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">connectés</p>
    </div>
    <div class="bg-gradient-to-br from-amber-500/20 to-orange-600/20 border border-amber-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Alertes</p><p class="text-3xl font-bold text-amber-400">{{ $alertesCritiques ?? $alertes }}</p></div><div class="w-14 h-14 bg-amber-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-bell text-amber-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">{{ $reglesFirewall }} règles FW</p>
    </div>
</div>
<!-- 3D + TRAFFIC -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-cube text-cyan-400"></i> Topologie 3D</h3></div>
        <div id="network3d" class="h-80 w-full"></div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-chart-area text-emerald-400"></i> Trafic (7j)</h3></div>
        <div class="p-4"><canvas id="trafficChart" height="260"></canvas></div>
    </div>
</div>
<!-- PERF + TOP -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-5">
        <h3 class="font-semibold mb-4 flex items-center gap-2"><i class="fas fa-heartbeat text-red-400"></i> Performance</h3>
        <div class="space-y-4">
            <div><div class="flex justify-between text-sm mb-1"><span class="text-slate-400">CPU</span><span class="text-cyan-400 font-mono">{{ $globalPerf['cpu'] }}%</span></div><div class="w-full bg-slate-700 rounded-full h-3"><div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-3 rounded-full" style="width:{{ min(100,$globalPerf['cpu']) }}%"></div></div></div>
            <div><div class="flex justify-between text-sm mb-1"><span class="text-slate-400">RAM</span><span class="text-purple-400 font-mono">{{ $globalPerf['memory'] }}%</span></div><div class="w-full bg-slate-700 rounded-full h-3"><div class="bg-gradient-to-r from-purple-500 to-pink-500 h-3 rounded-full" style="width:{{ min(100,$globalPerf['memory']) }}%"></div></div></div>
            <div><div class="flex justify-between text-sm mb-1"><span class="text-slate-400">Temp.</span><span class="text-amber-400 font-mono">{{ $globalPerf['temperature'] }}°C</span></div><div class="w-full bg-slate-700 rounded-full h-3"><div class="bg-gradient-to-r from-amber-500 to-red-500 h-3 rounded-full" style="width:{{ min(100,$globalPerf['temperature']) }}%"></div></div></div>
            <div class="pt-2 border-t border-slate-700 flex justify-between"><span class="text-slate-400 text-sm">Bande passante</span><span class="text-emerald-400 font-bold">{{ number_format($bandePassante,0) }} bps</span></div>
        </div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-5">
        <h3 class="font-semibold mb-4 flex items-center gap-2"><i class="fas fa-trophy text-amber-400"></i> Top Consommateurs</h3>
        <div class="space-y-3">
            @forelse($topConsommateurs ?? [] as $i=>$c)
            @php $mx=$topConsommateurs->max('total')?:1; @endphp
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-full bg-cyan-500/20 text-cyan-400 text-xs flex items-center justify-center font-bold">{{ $i+1 }}</span>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between text-sm"><span class="text-white truncate">{{ $c->nom }}</span><span class="text-slate-400 font-mono">{{ number_format($c->total,0) }}</span></div>
                    <div class="w-full bg-slate-700 rounded-full h-2 mt-1"><div class="bg-gradient-to-r from-cyan-500 to-blue-500 h-2 rounded-full" style="width:{{ ($c->total/$mx)*100 }}%"></div></div>
                </div>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Aucune donnée</p>
            @endforelse
        </div>
    </div>
</div>
<!-- INTERFACES + FIREWALL + ROUTES + ALERTES -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-ethernet text-cyan-400"></i> Interfaces Actives</h3></div>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-700/50"><tr><th class="px-4 py-3 text-left text-slate-400">Nom</th><th class="px-4 py-3 text-left text-slate-400">IP</th><th class="px-4 py-3 text-left text-slate-400">Type</th><th class="px-4 py-3 text-right text-slate-400">↓</th><th class="px-4 py-3 text-right text-slate-400">↑</th></tr></thead><tbody>
        @forelse($interfacesActives ?? [] as $if)
        <tr class="border-b border-slate-700/50 hover:bg-slate-700/30"><td class="px-4 py-2 text-white">{{ $if->nom }}</td><td class="px-4 py-2 text-slate-300 font-mono text-xs">{{ $if->adresse_ip }}</td><td class="px-4 py-2"><span class="px-2 py-0.5 rounded text-xs {{ $if->type==='wireless'?'bg-purple-500/20 text-purple-400':'bg-cyan-500/20 text-cyan-400' }}">{{ $if->type==='wireless'?'WiFi':'Eth' }}</span></td><td class="px-4 py-2 text-emerald-400 font-mono text-right">{{ number_format($if->debit_entrant,0) }}</td><td class="px-4 py-2 text-blue-400 font-mono text-right">{{ number_format($if->debit_sortant,0) }}</td></tr>
        @empty
        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Aucune interface</td></tr>
        @endforelse
        </tbody></table></div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl p-5">
        <h3 class="font-semibold mb-4 flex items-center gap-2"><i class="fas fa-shield-alt text-red-400"></i> Règles Firewall</h3>
        <div class="space-y-2 max-h-48 overflow-y-auto">
            @forelse($firewallRules ?? [] as $r)
            <div class="flex items-center gap-3 bg-slate-700/30 rounded-lg p-3"><span class="w-2 h-2 rounded-full {{ ($r->action??'accept')=='drop'?'bg-red-400':'bg-green-400' }}"></span><div class="flex-1"><p class="text-sm text-white">{{ $r->chain ?? 'forward' }} → {{ $r->action ?? 'accept' }}</p><p class="text-xs text-slate-400">{{ Str::limit($r->comment??'No comment',40) }}</p></div></div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Aucune règle active</p>
            @endforelse
        </div>
    </div>
</div>
<!-- ROUTES + ALERTES -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-route text-blue-400"></i> Routes Actives</h3></div>
        <div class="p-4 space-y-2 max-h-48 overflow-y-auto">
            @forelse($routesActives ?? [] as $r)
            <div class="flex items-center justify-between bg-slate-700/30 rounded-lg p-3">
                <div><p class="text-sm text-white">{{ $r->dst_address ?? '0.0.0.0/0' }} → {{ $r->gateway ?? '—' }}</p><p class="text-xs text-slate-400">{{ $r->routeur_nom ?? 'Routeur' }}</p></div>
                <span class="px-2 py-0.5 rounded text-xs {{ ($r->active??true)?'bg-green-500/20 text-green-400':'bg-red-500/20 text-red-400' }}">{{ ($r->active??true)?'Active':'Inactive' }}</span>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Aucune route</p>
            @endforelse
        </div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-exclamation-triangle text-amber-400"></i> Alertes Récentes</h3><span class="text-xs bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded">{{ count($alertesRecentes??[]) }}</span></div>
        <div class="p-4 space-y-2 max-h-48 overflow-y-auto">
            @forelse($alertesRecentes ?? [] as $a)
            <div class="flex items-start gap-3 bg-slate-700/30 rounded-lg p-3">
                <i class="fas fa-{{ $a->severite=='critique'?'exclamation-circle':'info-circle' }} text-{{ $a->severite=='critique'?'red':'amber' }}-400 mt-0.5"></i>
                <div class="flex-1"><p class="text-sm text-white">{{ $a->titre ?? $a->message ?? 'Alerte' }}</p><p class="text-xs text-slate-400">{{ $a->created_at?->diffForHumans() ?? '—' }}</p></div>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Aucune alerte récente</p>
            @endforelse
        </div>
    </div>
</div>
<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@push('scripts')
<script>
// Three.js Network Topology
const init3DNetwork = () => {
    const container = document.getElementById('network3d');
    if (!container) return;
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0f172a);
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    container.appendChild(renderer.domElement);

    // Add grid
    const gridHelper = new THREE.GridHelper(20, 20, 0x1e293b, 0x1e293b);
    scene.add(gridHelper);

    // Create router nodes
    const routers = {{ $routeurs->count() }};
    const nodes = [];
    for (let i = 0; i < Math.max(3, routers); i++) {
        const geometry = new THREE.SphereGeometry(0.5, 32, 32);
        const material = new THREE.MeshBasicMaterial({ color: i === 0 ? 0x06b6d4 : 0x8b5cf6 });
        const sphere = new THREE.Mesh(geometry, material);
        sphere.position.set(
            Math.cos(i * 2 * Math.PI / 3) * 4,
            1,
            Math.sin(i * 2 * Math.PI / 3) * 4
        );
        scene.add(sphere);
        nodes.push(sphere);

        // Connection lines
        if (i > 0) {
            const lineGeo = new THREE.BufferGeometry().setFromPoints([
                nodes[0].position,
                sphere.position
            ]);
            const lineMat = new THREE.LineBasicMaterial({ color: 0x06b6d4, opacity: 0.5, transparent: true });
            scene.add(new THREE.Line(lineGeo, lineMat));
        }
    }

    camera.position.set(0, 8, 10);
    camera.lookAt(0, 0, 0);

    // Auto rotation
    let rot = 0;
    const animate = () => {
        requestAnimationFrame(animate);
        rot += 0.005;
        camera.position.x = Math.sin(rot) * 10;
        camera.position.z = Math.cos(rot) * 10;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
    };
    animate();
};

// Traffic Chart
const initTrafficChart = () => {
    const ctx = document.getElementById('trafficChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {{ json_encode($traficGlobal->pluck('date')->map(fn($d)=>date('d/m',strtotime($d))) ?? []) }},
            datasets: [{
                label: 'Download',
                data: {{ json_encode($traficGlobal->pluck('download') ?? [500,800,600,900,1200,1000,1100]) }},
                borderColor: '#06b6d4',
                backgroundColor: 'rgba(6,182,212,0.1)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Upload',
                data: {{ json_encode($traficGlobal->pluck('upload') ?? [200,300,250,400,350,300,400]) }},
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139,92,246,0.1)',
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
    init3DNetwork();
    initTrafficChart();
});
</script>
@endpush
</div>
@endsection
