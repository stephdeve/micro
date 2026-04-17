@extends('layouts.app')
@section('title', 'Admin Service')
@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white p-6">
<!-- HEADER -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent"><i class="fas fa-building mr-2"></i>{{ $service->nom ?? 'Mon Service' }}</h1>
        <p class="text-slate-400 mt-1">Gestion des employés et consommation</p>
    </div>
    <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-full text-sm">Admin Service</span>
</div>
<!-- KPI CARDS -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-br from-cyan-500/20 to-blue-600/20 border border-cyan-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Employés</p><p class="text-3xl font-bold text-cyan-400">{{ $statsService['total_employes'] ?? 0 }}</p></div><div class="w-14 h-14 bg-cyan-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-users text-cyan-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">dans mon service</p>
    </div>
    <div class="bg-gradient-to-br from-purple-500/20 to-pink-600/20 border border-purple-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Zones WiFi</p><p class="text-3xl font-bold text-purple-400">{{ $zonesWifi ?? 0 }}</p></div><div class="w-14 h-14 bg-purple-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-wifi text-purple-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">assignées</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-500/20 to-green-600/20 border border-emerald-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Connectés</p><p class="text-3xl font-bold text-emerald-400">{{ $employesConnectes ?? 0 }}</p></div><div class="w-14 h-14 bg-emerald-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-signal text-emerald-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">maintenant</p>
    </div>
    <div class="bg-gradient-to-br from-amber-500/20 to-orange-600/20 border border-amber-500/30 rounded-xl p-5 hover:scale-[1.02] transition-transform">
        <div class="flex items-center justify-between"><div><p class="text-slate-400 text-sm">Quota utilisé</p><p class="text-3xl font-bold text-amber-400">{{ $quotaPourcent ?? 0 }}%</p></div><div class="w-14 h-14 bg-amber-500/20 rounded-xl flex items-center justify-center"><i class="fas fa-chart-pie text-amber-400 text-2xl"></i></div></div>
        <p class="text-xs text-slate-500 mt-2">{{ number_format($dataUsed ?? 0,1) }} / {{ number_format($quotaTotal ?? 0,1) }} Go</p>
    </div>
</div>
<!-- 3D + CONSO CHART -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-cube text-purple-400"></i> Topologie Employés 3D</h3></div>
        <div id="service3d" class="h-80 w-full"></div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-chart-area text-pink-400"></i> Consommation (7j)</h3></div>
        <div class="p-4"><canvas id="serviceChart" height="260"></canvas></div>
    </div>
</div>
<!-- EMPLOYES CONSO + CONNECTED -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-users text-cyan-400"></i> Employés & Consommation</h3><a href="{{ route('admin-service.employes') }}" class="text-xs text-cyan-400 hover:text-cyan-300">Voir tout</a></div>
        <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-slate-700/50"><tr><th class="px-4 py-3 text-left text-slate-400">Nom</th><th class="px-4 py-3 text-left text-slate-400">Fonction</th><th class="px-4 py-3 text-right text-slate-400">Consommé</th><th class="px-4 py-3 text-right text-slate-400">Quota</th></tr></thead><tbody>
        @forelse($employesConso ?? [] as $e)
        <tr class="border-b border-slate-700/50 hover:bg-slate-700/30"><td class="px-4 py-2"><div class="flex items-center gap-2"><div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center text-xs font-bold">{{ substr($e->user->name??'U',0,1) }}</div><span class="text-white">{{ $e->user->name ?? '—' }}</span></div></td><td class="px-4 py-2 text-slate-300">{{ $e->user->fonction ?? 'Employé' }}</td><td class="px-4 py-2 text-right text-white font-mono">{{ number_format($e->data_used_this_month,1) }} Go</td><td class="px-4 py-2 text-right"><div class="w-full bg-slate-700 rounded-full h-2 w-16 ml-auto"><div class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full" style="width:{{ min(100,$e->quota_pourcent) }}%"></div></div></td></tr>
        @empty
        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Aucun employé</td></tr>
        @endforelse
        </tbody></table></div>
    </div>
    <div class="bg-slate-800/50 border border-slate-700 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-700"><h3 class="font-semibold flex items-center gap-2"><i class="fas fa-wifi text-emerald-400"></i> Appareils Connectés Maintenant</h3></div>
        <div class="p-4 space-y-3 max-h-64 overflow-y-auto">
            @forelse($appareilsConnectes ?? [] as $a)
            <div class="flex items-center gap-3 bg-slate-700/30 rounded-lg p-3">
                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center"><i class="fas fa-{{ $a->device_type=='mobile'?'mobile-alt':($a->device_type=='tablet'?'tablet-alt':'laptop') }} text-emerald-400"></i></div>
                <div class="flex-1"><p class="text-sm text-white">{{ $a->user->name ?? '—' }}</p><p class="text-xs text-slate-400">{{ $a->wifiZone->nom ?? '—' }} • {{ $a->last_connected_at?->diffForHumans() ?? '—' }}</p></div>
                <span class="px-2 py-0.5 rounded text-xs bg-green-500/20 text-green-400">En ligne</span>
            </div>
            @empty
            <p class="text-slate-500 text-sm text-center py-4">Aucun appareil connecté</p>
            @endforelse
        </div>
    </div>
</div>
<!-- SCRIPTS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@push('scripts')
<script>
const initService3D = () => {
    const container = document.getElementById('service3d');
    if (!container) return;
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0f172a);
    const camera = new THREE.PerspectiveCamera(75, container.clientWidth/container.clientHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    container.appendChild(renderer.domElement);

    // Central hub
    const hubGeo = new THREE.SphereGeometry(1, 32, 32);
    const hubMat = new THREE.MeshBasicMaterial({ color: 0x8b5cf6 });
    const hub = new THREE.Mesh(hubGeo, hubMat);
    hub.position.set(0, 1, 0);
    scene.add(hub);

    // Employee nodes
    const empCount = {{ count($employesConso ?? []) }} || 5;
    for (let i = 0; i < Math.max(3, empCount); i++) {
        const geo = new THREE.SphereGeometry(0.4, 16, 16);
        const mat = new THREE.MeshBasicMaterial({ color: 0x06b6d4 });
        const node = new THREE.Mesh(geo, mat);
        node.position.set(Math.cos(i*2*Math.PI/3)*3, 0.5, Math.sin(i*2*Math.PI/3)*3);
        scene.add(node);

        const lineGeo = new THREE.BufferGeometry().setFromPoints([hub.position, node.position]);
        const lineMat = new THREE.LineBasicMaterial({ color: 0x8b5cf6, opacity: 0.3, transparent: true });
        scene.add(new THREE.Line(lineGeo, lineMat));
    }

    camera.position.set(0, 6, 8);
    let rot = 0;
    const animate = () => {
        requestAnimationFrame(animate);
        rot += 0.005;
        camera.position.x = Math.sin(rot) * 8;
        camera.position.z = Math.cos(rot) * 8;
        camera.lookAt(0, 0, 0);
        renderer.render(scene, camera);
    };
    animate();
};

const initServiceChart = () => {
    const ctx = document.getElementById('serviceChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {{ json_encode($conso7Jours->pluck('date')->map(fn($d)=>date('d/m',strtotime($d))) ?? []) }},
            datasets: [{
                label: 'Download',
                data: {{ json_encode($conso7Jours->pluck('download') ?? [200,350,280,400,320,450,380]) }},
                backgroundColor: 'rgba(139,92,246,0.6)',
                borderColor: '#8b5cf6',
                borderWidth: 1
            }, {
                label: 'Upload',
                data: {{ json_encode($conso7Jours->pluck('upload') ?? [100,150,120,180,140,200,160]) }},
                backgroundColor: 'rgba(236,72,153,0.6)',
                borderColor: '#ec4899',
                borderWidth: 1
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
    initService3D();
    initServiceChart();
});
</script>
@endpush
</div>
@endsection
