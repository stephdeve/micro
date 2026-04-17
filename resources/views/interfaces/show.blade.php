@extends('layouts.app')

@section('title', 'Détail Interface - ' . $interface->nom)

@section('content')
<div class="min-h-[calc(100vh-1.5rem)] bg-slate-900 text-white py-6 pl-20 pr-4">
    
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('interfaces.index') }}" class="p-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center justify-center group">
                <i class="fas fa-arrow-left text-cyan-400 group-hover:text-cyan-300"></i>
            </a>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 via-blue-400 to-purple-500 bg-clip-text text-transparent">
                    <i class="fas fa-ethernet mr-3"></i>{{ $interface->nom }}
                </h1>
                <p class="text-slate-400 mt-1 flex items-center gap-2">
                    <i class="fas fa-server text-cyan-400/70"></i>
                    Interface {{ $interface->type }} 
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $interface->statut === 'actif' ? 'bg-emerald-500/20 text-emerald-400' : ($interface->statut === 'erreur' ? 'bg-rose-500/20 text-rose-400' : 'bg-slate-500/20 text-slate-400') }}">
                        {{ ucfirst($interface->statut) }}
                    </span>
                </p>
            </div>
        </div>
        
        <div class="flex gap-3">
            <a href="{{ route('interfaces.graph', $interface) }}" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center gap-2 shadow-lg shadow-cyan-500/25">
                <i class="fas fa-chart-line"></i>
                <span>Graphiques</span>
            </a>
            <a href="{{ route('interfaces.edit', $interface) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl transition flex items-center gap-2">
                <i class="fas fa-edit text-amber-400"></i>
                <span>Modifier</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Statut -->
        <div class="bg-gradient-to-br {{ $interface->statut === 'actif' ? 'from-emerald-500/10 to-emerald-600/5 border-emerald-500/20' : ($interface->statut === 'erreur' ? 'from-rose-500/10 to-rose-600/5 border-rose-500/20' : 'from-slate-500/10 to-slate-600/5 border-slate-500/20') }} border rounded-2xl p-5 relative overflow-hidden group hover:border-opacity-40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 {{ $interface->statut === 'actif' ? 'bg-emerald-500/10' : ($interface->statut === 'erreur' ? 'bg-rose-500/10' : 'bg-slate-500/10') }} rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:opacity-20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 {{ $interface->statut === 'actif' ? 'bg-emerald-500/20' : ($interface->statut === 'erreur' ? 'bg-rose-500/20' : 'bg-slate-500/20') }} rounded-xl flex items-center justify-center">
                        <i class="fas fa-circle {{ $interface->statut === 'actif' ? 'text-emerald-400' : ($interface->statut === 'erreur' ? 'text-rose-400' : 'text-slate-400') }} text-xl"></i>
                    </div>
                    <span class="{{ $interface->statut === 'actif' ? 'text-emerald-400' : ($interface->statut === 'erreur' ? 'text-rose-400' : 'text-slate-400') }} text-sm font-medium">Statut</span>
                </div>
                <div class="text-2xl font-bold text-white">{{ ucfirst($interface->statut) }}</div>
                <div class="text-slate-400 text-sm mt-1">{{ $interface->statut === 'actif' ? 'Opérationnel' : ($interface->statut === 'erreur' ? 'Problème détecté' : 'Inactif') }}</div>
            </div>
        </div>

        <!-- Type -->
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-600/5 border border-cyan-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-cyan-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-cyan-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-cyan-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-network-wired text-cyan-400 text-xl"></i>
                    </div>
                    <span class="text-cyan-400 text-sm font-medium">Type</span>
                </div>
                <div class="text-2xl font-bold text-white capitalize">{{ $interface->type }}</div>
                <div class="text-cyan-400/70 text-sm mt-1">{{ $interface->type === 'ethernet' ? 'Connexion filaire' : ($interface->type === 'wifi' ? 'Sans fil' : 'Interface virtuelle') }}</div>
            </div>
        </div>

        <!-- Débit -->
        <div class="bg-gradient-to-br from-amber-500/10 to-orange-600/5 border border-amber-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-amber-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-amber-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-tachometer-alt text-amber-400 text-xl"></i>
                    </div>
                    <span class="text-amber-400 text-sm font-medium">Débit</span>
                </div>
                <div class="text-2xl font-bold text-white">{{ $interface->debit_entrant ?? 0 }}/{{ $interface->debit_sortant ?? 0 }}</div>
                <div class="text-amber-400/70 text-sm mt-1">Mbps (↓Rx / ↑Tx)</div>
            </div>
        </div>

        <!-- Routeur -->
        <div class="bg-gradient-to-br from-purple-500/10 to-pink-600/5 border border-purple-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-purple-500/40 transition">
            <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-purple-500/20 transition"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-server text-purple-400 text-xl"></i>
                    </div>
                    <span class="text-purple-400 text-sm font-medium">Routeur</span>
                </div>
                <div class="text-xl font-bold text-white truncate">{{ $interface->routeur->nom ?? 'N/A' }}</div>
                <div class="text-purple-400/70 text-sm mt-1">{{ $interface->routeur->ip ?? '—' }}</div>
            </div>
        </div>
    </div>

    <!-- 3D Interface Visualization -->
    <div class="mb-8 bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-cube text-cyan-400"></i>
                Visualisation 3D de l'interface
            </h3>
            <div class="flex items-center gap-3">
                <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Three.js</span>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    {{ $interface->statut === 'actif' ? 'Connecté' : 'Déconnecté' }}
                </span>
            </div>
        </div>
        <div id="interface3D" class="h-80 w-full"></div>
    </div>

    <!-- Main Info Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Network Info -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-info-circle text-cyan-400"></i>
                    Informations réseau
                </h3>
            </div>
            <div class="p-4 space-y-4">
                <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ethernet text-cyan-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Adresse MAC</div>
                            <div class="font-mono text-white">{{ $interface->adresse_mac ?? 'Non définie' }}</div>
                        </div>
                    </div>
                    <button onclick="copyToClipboard('{{ $interface->adresse_mac }}')" class="p-2 hover:bg-slate-700 rounded-lg transition" title="Copier">
                        <i class="fas fa-copy text-slate-400"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-globe text-emerald-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Adresse IP</div>
                            <div class="font-mono text-white">{{ $interface->adresse_ip ?? 'Non définie' }}</div>
                        </div>
                    </div>
                    <button onclick="copyToClipboard('{{ $interface->adresse_ip }}')" class="p-2 hover:bg-slate-700 rounded-lg transition" title="Copier">
                        <i class="fas fa-copy text-slate-400"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-network-wired text-amber-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">VLAN ID</div>
                            <div class="font-mono text-white">{{ $interface->vlan_id ?? 'Aucun' }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 bg-slate-900/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-purple-400"></i>
                        </div>
                        <div>
                            <div class="text-sm text-slate-400">Dernière mise à jour</div>
                            <div class="text-white">{{ $interface->updated_at ? $interface->updated_at->format('d/m/Y H:i') : '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Traffic Info -->
        <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden">
            <div class="p-4 border-b border-slate-700 flex items-center justify-between">
                <h3 class="font-semibold flex items-center gap-2">
                    <i class="fas fa-exchange-alt text-emerald-400"></i>
                    Trafic actuel
                </h3>
                <span class="text-xs text-slate-400 bg-slate-700 px-2 py-1 rounded-lg">Temps réel</span>
            </div>
            <div class="p-4">
                <!-- RX Progress -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-400 flex items-center gap-2">
                            <i class="fas fa-arrow-down text-cyan-400"></i>
                            Réception (Rx)
                        </span>
                        <span class="text-cyan-400 font-mono font-medium">{{ $interface->debit_entrant ?? 0 }} Mbps</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-3 overflow-hidden">
                        @php
                            $rxPct = min(100, (($interface->debit_entrant ?? 0) / 100) * 100);
                            $rxColor = $rxPct > 80 ? 'bg-rose-400' : ($rxPct > 50 ? 'bg-amber-400' : 'bg-cyan-400');
                        @endphp
                        <div class="{{ $rxColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $rxPct }}%"></div>
                    </div>
                </div>

                <!-- TX Progress -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-400 flex items-center gap-2">
                            <i class="fas fa-arrow-up text-emerald-400"></i>
                            Transmission (Tx)
                        </span>
                        <span class="text-emerald-400 font-mono font-medium">{{ $interface->debit_sortant ?? 0 }} Mbps</span>
                    </div>
                    <div class="w-full bg-slate-700 rounded-full h-3 overflow-hidden">
                        @php
                            $txPct = min(100, (($interface->debit_sortant ?? 0) / 100) * 100);
                            $txColor = $txPct > 80 ? 'bg-rose-400' : ($txPct > 50 ? 'bg-amber-400' : 'bg-emerald-400');
                        @endphp
                        <div class="{{ $txColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $txPct }}%"></div>
                    </div>
                </div>

                <!-- Total -->
                <div class="p-4 bg-slate-900/50 rounded-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Débit total</span>
                        <span class="text-2xl font-bold text-white">{{ ($interface->debit_entrant ?? 0) + ($interface->debit_sortant ?? 0) }} <span class="text-sm text-slate-400">Mbps</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden mb-8">
        <div class="p-4 border-b border-slate-700">
            <h3 class="font-semibold flex items-center gap-2">
                <i class="fas fa-align-left text-amber-400"></i>
                Description
            </h3>
        </div>
        <div class="p-4">
            <p class="text-slate-300 leading-relaxed">
                {{ $interface->description ?? 'Aucune description disponible pour cette interface.' }}
            </p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('interfaces.edit', $interface) }}" class="flex-1 min-w-[200px] px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-400 hover:to-orange-500 rounded-xl font-medium transition flex items-center justify-center gap-3 shadow-lg shadow-amber-500/25">
            <i class="fas fa-edit text-xl"></i>
            <span>Modifier l'interface</span>
        </a>
        
        <a href="{{ route('interfaces.graph', $interface) }}" class="flex-1 min-w-[200px] px-6 py-4 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 rounded-xl font-medium transition flex items-center justify-center gap-3 shadow-lg shadow-cyan-500/25">
            <i class="fas fa-chart-line text-xl"></i>
            <span>Voir les graphiques</span>
        </a>
        
        <form method="POST" action="{{ route('interfaces.destroy', $interface) }}" class="flex-1 min-w-[200px]" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette interface ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full px-6 py-4 bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-400 hover:to-pink-500 rounded-xl font-medium transition flex items-center justify-center gap-3 shadow-lg shadow-rose-500/25">
                <i class="fas fa-trash-alt text-xl"></i>
                <span>Supprimer</span>
            </button>
        </form>
    </div>

</div>

<!-- Three.js for 3D Interface Visualization -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function() {
    const container = document.getElementById('interface3D');
    if (!container) return;

    // Scene setup
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x1e293b);
    
    const camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 0.1, 1000);
    camera.position.set(0, 3, 6);
    camera.lookAt(0, 0, 0);
    
    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
    renderer.setSize(container.clientWidth, container.clientHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    container.appendChild(renderer.domElement);

    // Lights
    const ambientLight = new THREE.AmbientLight(0x404040, 1.2);
    scene.add(ambientLight);
    
    const pointLight1 = new THREE.PointLight(0x06b6d4, 1.5, 20);
    pointLight1.position.set(5, 8, 5);
    scene.add(pointLight1);
    
    const pointLight2 = new THREE.PointLight(0x10b981, 1.2, 20);
    pointLight2.position.set(-5, 3, -5);
    scene.add(pointLight2);

    // Grid floor
    const gridHelper = new THREE.GridHelper(10, 10, 0x334155, 0x1e293b);
    scene.add(gridHelper);

    // Interface type from PHP
    const interfaceType = '{{ $interface->type }}';
    const isActive = '{{ $interface->statut }}' === 'actif';
    
    // Color based on status
    const mainColor = isActive ? 0x06b6d4 : 0x64748b; // cyan or slate
    const glowColor = isActive ? 0x00d4ff : 0x94a3b8;

    // Create interface representation based on type
    let interfaceMesh;
    
    if (interfaceType === 'ethernet') {
        // Ethernet port representation
        const group = new THREE.Group();
        
        // Base (the port body)
        const baseGeometry = new THREE.BoxGeometry(2, 1.2, 0.8);
        const baseMaterial = new THREE.MeshPhongMaterial({ 
            color: 0x334155,
            specular: 0x1e293b,
            shininess: 30
        });
        const base = new THREE.Mesh(baseGeometry, baseMaterial);
        base.position.y = 0.6;
        group.add(base);
        
        // Port hole
        const holeGeometry = new THREE.BoxGeometry(1.2, 0.8, 0.2);
        const holeMaterial = new THREE.MeshBasicMaterial({ color: 0x0f172a });
        const hole = new THREE.Mesh(holeGeometry, holeMaterial);
        hole.position.set(0, 0.7, 0.41);
        group.add(hole);
        
        // LED indicators
        const ledGeometry = new THREE.SphereGeometry(0.1, 16, 16);
        const ledMaterial = new THREE.MeshBasicMaterial({ 
            color: isActive ? 0x10b981 : 0xef4444,
            emissive: isActive ? 0x10b981 : 0xef4444,
            emissiveIntensity: isActive ? 1 : 0.5
        });
        
        const led1 = new THREE.Mesh(ledGeometry, ledMaterial);
        led1.position.set(-0.7, 0.9, 0.42);
        group.add(led1);
        
        const led2 = new THREE.Mesh(ledGeometry, ledMaterial);
        led2.position.set(0.7, 0.9, 0.42);
        group.add(led2);
        
        // Connection glow (if active)
        if (isActive) {
            const glowGeometry = new THREE.PlaneGeometry(1, 0.6);
            const glowMaterial = new THREE.MeshBasicMaterial({ 
                color: 0x06b6d4,
                transparent: true,
                opacity: 0.3,
                side: THREE.DoubleSide
            });
            const glow = new THREE.Mesh(glowGeometry, glowMaterial);
            glow.position.set(0, 0.7, 0.5);
            group.add(glow);
        }
        
        // Connection cable
        const cableGeometry = new THREE.CylinderGeometry(0.15, 0.15, 3, 16);
        const cableMaterial = new THREE.MeshPhongMaterial({ 
            color: isActive ? 0x06b6d4 : 0x64748b,
            emissive: isActive ? mainColor : 0,
            emissiveIntensity: isActive ? 0.3 : 0
        });
        const cable = new THREE.Mesh(cableGeometry, cableMaterial);
        cable.rotation.x = Math.PI / 2;
        cable.position.set(0, 0.7, 2.3);
        group.add(cable);
        
        // Cable connector
        const connectorGeometry = new THREE.BoxGeometry(0.5, 0.5, 1);
        const connectorMaterial = new THREE.MeshPhongMaterial({ color: 0x475569 });
        const connector = new THREE.Mesh(connectorGeometry, connectorMaterial);
        connector.position.set(0, 0.7, 0.9);
        group.add(connector);
        
        // Gold pins
        for (let i = 0; i < 8; i++) {
            const pinGeometry = new THREE.BoxGeometry(0.05, 0.05, 0.3);
            const pinMaterial = new THREE.MeshBasicMaterial({ 
                color: 0xfbbf24,
                emissive: 0xfbbf24,
                emissiveIntensity: 0.5
            });
            const pin = new THREE.Mesh(pinGeometry, pinMaterial);
            pin.position.set(-0.35 + i * 0.1, 0.65, 0.5);
            group.add(pin);
        }
        
        interfaceMesh = group;
        
    } else if (interfaceType === 'wifi') {
        // WiFi antenna representation
        const group = new THREE.Group();
        
        // Router body
        const bodyGeometry = new THREE.BoxGeometry(2, 0.6, 1.5);
        const bodyMaterial = new THREE.MeshPhongMaterial({ 
            color: 0x334155,
            specular: 0x1e293b,
            shininess: 30
        });
        const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
        body.position.y = 0.3;
        group.add(body);
        
        // Status LED on router
        const statusLedGeometry = new THREE.SphereGeometry(0.08, 16, 16);
        const statusLedMaterial = new THREE.MeshBasicMaterial({ 
            color: isActive ? 0x10b981 : 0xef4444,
            emissive: isActive ? 0x10b981 : 0xef4444,
            emissiveIntensity: isActive ? 1 : 0.5
        });
        const statusLed = new THREE.Mesh(statusLedGeometry, statusLedMaterial);
        statusLed.position.set(0.8, 0.5, 0.6);
        group.add(statusLed);
        
        // Antennas
        const antennaPositions = [
            [-0.7, 0.6, 0.5],
            [0.7, 0.6, 0.5]
        ];
        
        antennaPositions.forEach((pos, idx) => {
            const antennaGroup = new THREE.Group();
            
            // Antenna base
            const baseGeo = new THREE.CylinderGeometry(0.15, 0.2, 0.3, 16);
            const baseMat = new THREE.MeshPhongMaterial({ color: 0x475569 });
            const base = new THREE.Mesh(baseGeo, baseMat);
            antennaGroup.add(base);
            
            // Antenna rod
            const rodGeo = new THREE.CylinderGeometry(0.08, 0.08, 2, 16);
            const rodMat = new THREE.MeshPhongMaterial({ 
                color: 0x64748b,
                emissive: isActive ? mainColor : 0,
                emissiveIntensity: isActive ? 0.2 : 0
            });
            const rod = new THREE.Mesh(rodGeo, rodMat);
            rod.position.y = 1;
            antennaGroup.add(rod);
            
            // Antenna tip
            const tipGeo = new THREE.SphereGeometry(0.12, 16, 16);
            const tipMat = new THREE.MeshBasicMaterial({ 
                color: isActive ? 0x06b6d4 : 0x64748b,
                emissive: isActive ? 0x06b6d4 : 0,
                emissiveIntensity: isActive ? 0.8 : 0
            });
            const tip = new THREE.Mesh(tipGeo, tipMat);
            tip.position.y = 2;
            antennaGroup.add(tip);
            
            antennaGroup.position.set(...pos);
            // Slight angle
            antennaGroup.rotation.z = idx === 0 ? 0.2 : -0.2;
            group.add(antennaGroup);
        });
        
        // WiFi signal waves (if active)
        if (isActive) {
            const waves = [];
            for (let i = 0; i < 3; i++) {
                const waveGeometry = new THREE.TorusGeometry(1 + i * 0.8, 0.03, 8, 32, Math.PI);
                const waveMaterial = new THREE.MeshBasicMaterial({ 
                    color: 0x06b6d4,
                    transparent: true,
                    opacity: 0.4 - i * 0.1,
                    side: THREE.DoubleSide
                });
                const wave = new THREE.Mesh(waveGeometry, waveMaterial);
                wave.rotation.x = -Math.PI / 2;
                wave.position.set(0, 0.5, 2);
                wave.userData = { speed: 0.02 + i * 0.01, offset: i * 2 };
                group.add(wave);
                waves.push(wave);
            }
            interfaceMesh = { mesh: group, waves: waves };
        } else {
            interfaceMesh = { mesh: group };
        }
        
    } else {
        // Generic network interface
        const group = new THREE.Group();
        
        // Main card
        const cardGeometry = new THREE.BoxGeometry(2.5, 0.2, 1.5);
        const cardMaterial = new THREE.MeshPhongMaterial({ 
            color: 0x334155,
            specular: 0x1e293b,
            shininess: 50,
            emissive: isActive ? mainColor : 0,
            emissiveIntensity: isActive ? 0.1 : 0
        });
        const card = new THREE.Mesh(cardGeometry, cardMaterial);
        card.position.y = 0.5;
        group.add(card);
        
        // Connection port
        const portGeometry = new THREE.BoxGeometry(0.8, 0.3, 0.2);
        const portMaterial = new THREE.MeshPhongMaterial({ color: 0x1e293b });
        const port = new THREE.Mesh(portGeometry, portMaterial);
        port.position.set(0, 0.5, 0.8);
        group.add(port);
        
        // LED strip
        for (let i = 0; i < 5; i++) {
            const ledGeo = new THREE.SphereGeometry(0.06, 16, 16);
            const ledMat = new THREE.MeshBasicMaterial({ 
                color: isActive && i < 3 ? 0x06b6d4 : 0x475569,
                emissive: isActive && i < 3 ? 0x06b6d4 : 0,
                emissiveIntensity: isActive && i < 3 ? 1 : 0
            });
            const led = new THREE.Mesh(ledGeo, ledMat);
            led.position.set(-0.8 + i * 0.4, 0.65, 0.5);
            group.add(led);
        }
        
        // Data flow particles (if active)
        if (isActive) {
            const particles = [];
            for (let i = 0; i < 10; i++) {
                const particleGeo = new THREE.SphereGeometry(0.05, 8, 8);
                const particleMat = new THREE.MeshBasicMaterial({ 
                    color: 0x06b6d4,
                    transparent: true,
                    opacity: 0.8
                });
                const particle = new THREE.Mesh(particleGeo, particleMat);
                particle.position.set(
                    (Math.random() - 0.5) * 2,
                    0.5 + Math.random() * 0.5,
                    1 + Math.random()
                );
                particle.userData = {
                    speed: 0.02 + Math.random() * 0.03,
                    resetZ: 3
                };
                group.add(particle);
                particles.push(particle);
            }
            interfaceMesh = { mesh: group, particles: particles };
        } else {
            interfaceMesh = { mesh: group };
        }
    }
    
    const mainMesh = interfaceMesh.mesh || interfaceMesh;
    scene.add(mainMesh);
    
    // Orbiting ring
    const ringGeometry = new THREE.TorusGeometry(3.5, 0.05, 8, 64);
    const ringMaterial = new THREE.MeshBasicMaterial({ 
        color: mainColor,
        transparent: true,
        opacity: 0.3
    });
    const ring = new THREE.Mesh(ringGeometry, ringMaterial);
    ring.rotation.x = Math.PI / 2;
    ring.position.y = 0.5;
    scene.add(ring);
    
    // Second ring (tilted)
    const ring2Geometry = new THREE.TorusGeometry(4, 0.03, 8, 64);
    const ring2Material = new THREE.MeshBasicMaterial({ 
        color: glowColor,
        transparent: true,
        opacity: 0.2
    });
    const ring2 = new THREE.Mesh(ring2Geometry, ring2Material);
    ring2.rotation.x = Math.PI / 2.5;
    ring2.position.y = 0.5;
    scene.add(ring2);
    
    // Floating particles around
    const ambientParticles = [];
    for (let i = 0; i < 15; i++) {
        const particleGeo = new THREE.SphereGeometry(0.03, 8, 8);
        const particleMat = new THREE.MeshBasicMaterial({ 
            color: glowColor,
            transparent: true,
            opacity: 0.6
        });
        const particle = new THREE.Mesh(particleGeo, particleMat);
        
        const angle = Math.random() * Math.PI * 2;
        const radius = 3 + Math.random() * 2;
        particle.position.set(
            radius * Math.cos(angle),
            0.5 + Math.random() * 2,
            radius * Math.sin(angle)
        );
        particle.userData = {
            angle: angle,
            radius: radius,
            speed: 0.005 + Math.random() * 0.01,
            yOffset: Math.random() * Math.PI * 2
        };
        scene.add(particle);
        ambientParticles.push(particle);
    }
    
    // Animation
    let isActive = true;
    const animate = () => {
        if (!isActive) return;
        requestAnimationFrame(animate);
        
        // Rotate rings
        ring.rotation.z += 0.005;
        ring2.rotation.z -= 0.003;
        
        // Float main mesh
        const time = Date.now() * 0.001;
        mainMesh.position.y = Math.sin(time) * 0.1;
        
        // Animate WiFi waves
        if (interfaceMesh.waves) {
            interfaceMesh.waves.forEach((wave, i) => {
                wave.scale.x = 1 + Math.sin(time * 2 + wave.userData.offset) * 0.1;
                wave.scale.z = 1 + Math.sin(time * 2 + wave.userData.offset) * 0.1;
                wave.material.opacity = 0.4 - i * 0.1 + Math.sin(time * 3) * 0.1;
            });
        }
        
        // Animate data particles
        if (interfaceMesh.particles) {
            interfaceMesh.particles.forEach(p => {
                p.position.z += p.userData.speed;
                if (p.position.z > p.userData.resetZ) {
                    p.position.z = 0.5;
                }
            });
        }
        
        // Animate ambient particles
        ambientParticles.forEach(p => {
            p.userData.angle += p.userData.speed;
            p.position.x = p.userData.radius * Math.cos(p.userData.angle);
            p.position.z = p.userData.radius * Math.sin(p.userData.angle);
            p.position.y = 1 + Math.sin(time + p.userData.yOffset) * 0.5;
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

// Copy to clipboard function
function copyToClipboard(text) {
    if (!text || text === 'Non définie') return;
    navigator.clipboard.writeText(text).then(() => {
        // Show toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-emerald-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-fade-in';
        toast.innerHTML = '<i class="fas fa-check mr-2"></i>Copié !';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    });
}
</script>
@endsection
