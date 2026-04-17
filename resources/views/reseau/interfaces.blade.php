@extends('layouts.app')

@section('title', 'Interfaces — ' . $routeur->nom)

@php
function fmtBytes($b) {
    $b = (float)($b ?? 0);
    if ($b <= 0) return '0 B';
    $u = ['B','KB','MB','GB','TB'];
    $i = min(floor(log($b, 1024)), 4);
    return round($b / pow(1024, $i), 2) . ' ' . $u[$i];
}
$typeIcon  = ['ethernet'=>'fa-ethernet','wifi'=>'fa-wifi','bridge'=>'fa-project-diagram','vlan'=>'fa-sitemap'];
$typeColor = ['ethernet'=>'cyan','wifi'=>'emerald','bridge'=>'amber','vlan'=>'purple'];
$statColor = ['actif'=>'emerald','inactif'=>'rose','erreur'=>'orange'];

$total    = count($interfacesWithIps);
$actives  = collect($interfacesWithIps)->where('statut','actif')->count();
$inactifs = $total - $actives;
$rxTotal  = collect($interfacesWithIps)->sum('debit_entrant');
$txTotal  = collect($interfacesWithIps)->sum('debit_sortant');
@endphp

@section('content')
<div class="min-h-screen bg-slate-900 text-white py-6 pl-20 pr-4">

  {{-- ===== TOAST ===== --}}
  <div id="toast" class="fixed top-6 right-6 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

  {{-- ===== HEADER ===== --}}
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
    <div>
      <div class="flex items-center gap-3 mb-1">
        <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-cyan-500/30">
          <i class="fas fa-ethernet text-white text-xl"></i>
        </div>
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">
            Interfaces
          </h1>
          <p class="text-slate-400 text-sm">{{ $routeur->nom }} — {{ $routeur->adresse_ip }}</p>
        </div>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <span class="px-3 py-1 text-xs rounded-full {{ $routeur->statut === 'en_ligne' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
        <span class="inline-block w-2 h-2 rounded-full {{ $routeur->statut === 'en_ligne' ? 'bg-emerald-400' : 'bg-rose-400' }} animate-pulse mr-1"></span>
        {{ $routeur->statut === 'en_ligne' ? 'En ligne' : 'Hors ligne' }}
      </span>
      <button id="syncBtn" onclick="syncInterfaces()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-cyan-500/50 rounded-xl text-sm transition flex items-center gap-2">
        <i class="fas fa-sync" id="syncIcon"></i> Synchroniser
      </button>
      <a href="{{ route('routeurs.show', $routeur) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-sm transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
    </div>
  </div>

  {{-- ===== STATS CARDS ===== --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach([
      ['Total','fas fa-ethernet', $total, 'interfaces', 'cyan'],
      ['Actives','fas fa-circle-check', $actives, 'en ligne', 'emerald'],
      ['Inactives','fas fa-circle-xmark', $inactifs, 'hors ligne', 'rose'],
      ['Trafic RX','fas fa-arrow-down', fmtBytes($rxTotal), 'total reçu', 'amber'],
    ] as [$label, $icon, $val, $sub, $col])
    <div class="bg-gradient-to-br from-{{ $col }}-500/10 to-{{ $col }}-600/5 border border-{{ $col }}-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-{{ $col }}-500/40 transition">
      <div class="absolute top-0 right-0 w-28 h-28 bg-{{ $col }}-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl group-hover:bg-{{ $col }}-500/20 transition"></div>
      <div class="relative flex items-start gap-3">
        <div class="w-10 h-10 bg-{{ $col }}-500/20 rounded-xl flex items-center justify-center shrink-0">
          <i class="{{ $icon }} text-{{ $col }}-400"></i>
        </div>
        <div>
          <div class="text-2xl font-bold text-white">{{ $val }}</div>
          <div class="text-{{ $col }}-400/70 text-xs mt-0.5">{{ $label }} — {{ $sub }}</div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- ===== INTERFACES GRID ===== --}}
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($interfacesWithIps as $iface)
    @php
      $ic  = $typeIcon[$iface->type]  ?? 'fa-network-wired';
      $col = $typeColor[$iface->type] ?? 'cyan';
      $sc  = $statColor[$iface->statut] ?? 'slate';
      $apiId = $iface->mikrotik_id ?? $iface->nom;
    @endphp
    <div class="interface-card bg-slate-800/60 border border-slate-700 hover:border-{{ $col }}-500/50 rounded-2xl p-5 transition-all group relative overflow-hidden"
         data-name="{{ $iface->nom }}" data-routeur="{{ $routeur->id }}">
      {{-- glow --}}
      <div class="absolute inset-0 bg-gradient-to-br from-{{ $col }}-500/0 to-transparent opacity-0 group-hover:opacity-10 transition pointer-events-none rounded-2xl"></div>

      {{-- Header --}}
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-{{ $col }}-500/20 rounded-xl flex items-center justify-center">
            <i class="fas {{ $ic }} text-{{ $col }}-400 text-xl"></i>
          </div>
          <div>
            <h3 class="font-bold text-white iface-name">{{ $iface->nom }}</h3>
            <span class="text-xs text-{{ $col }}-400 uppercase tracking-wider">{{ $iface->type }}</span>
          </div>
        </div>
        {{-- Status badge --}}
        <span class="iface-status flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
          {{ $iface->statut === 'actif' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
          <span class="w-1.5 h-1.5 rounded-full {{ $iface->statut === 'actif' ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }}"></span>
          {{ ucfirst($iface->statut) }}
        </span>
      </div>

      {{-- Details --}}
      <div class="space-y-1.5 text-sm mb-4 bg-slate-900/50 rounded-xl p-3">
        <div class="flex justify-between items-center">
          <span class="text-slate-400">MAC</span>
          <span class="text-cyan-400 font-mono text-xs">{{ $iface->adresse_mac ?? '—' }}</span>
        </div>
        <div class="flex justify-between items-start">
          <span class="text-slate-400">IP(s)</span>
          <div class="text-right">
            @if(!empty($iface->ip_addresses))
              @foreach($iface->ip_addresses as $ip)
                <span class="inline-block bg-blue-500/20 text-blue-300 px-1.5 py-0.5 rounded text-xs font-mono">{{ $ip['address'] }}{{ $ip['dynamic'] ? ' <small>(DHCP)</small>' : '' }}</span>
              @endforeach
            @else
              <span class="text-slate-500 text-xs">Aucune IP</span>
            @endif
          </div>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-slate-400">RX / TX</span>
          <span class="text-white text-xs font-mono iface-rx-tx">{{ fmtBytes($iface->debit_entrant) }} / {{ fmtBytes($iface->debit_sortant) }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-slate-400">MTU</span>
          <span class="text-slate-300 text-xs">{{ $iface->mtu ?? '1500' }}</span>
        </div>
      </div>

      {{-- Sparkline --}}
      <div class="mb-4">
        <div class="flex justify-between text-xs text-slate-500 mb-1">
          <span><i class="fas fa-chart-area mr-1"></i>Trafic temps réel</span>
          <span class="iface-live-bps text-cyan-400">— Mbps</span>
        </div>
        <canvas class="iface-chart w-full h-12 rounded" height="48"></canvas>
      </div>

      {{-- Actions --}}
      <div class="flex gap-2 flex-wrap">
        @if($iface->statut === 'actif')
          <button onclick="toggleIface('{{ $apiId }}','{{ $iface->nom }}',false)"
            title="Désactiver"
            class="flex-1 py-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 hover:border-rose-400 rounded-lg text-xs transition flex items-center justify-center gap-1">
            <i class="fas fa-pause"></i> Désactiver
          </button>
        @else
          <button onclick="toggleIface('{{ $apiId }}','{{ $iface->nom }}',true)"
            title="Activer"
            class="flex-1 py-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 hover:border-emerald-400 rounded-lg text-xs transition flex items-center justify-center gap-1">
            <i class="fas fa-play"></i> Activer
          </button>
        @endif
        <button onclick="openRename('{{ $apiId }}','{{ $iface->nom }}')" title="Renommer"
          class="p-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white border border-slate-600 rounded-lg text-xs transition"><i class="fas fa-edit"></i></button>
        <button onclick="openConfigure('{{ $apiId }}','{{ $iface->nom }}')" title="Configurer MTU"
          class="p-2 bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white border border-slate-600 rounded-lg text-xs transition"><i class="fas fa-cog"></i></button>
        <button onclick="openAssignIp('{{ $iface->nom }}')" title="Assigner IP"
          class="p-2 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 rounded-lg text-xs transition"><i class="fas fa-network-wired"></i></button>
        <button onclick="openDetails('{{ $apiId }}','{{ $iface->nom }}')" title="Détails"
          class="p-2 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 rounded-lg text-xs transition"><i class="fas fa-info-circle"></i></button>
      </div>
    </div>
    @empty
    <div class="col-span-full text-center py-20">
      <div class="w-20 h-20 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <i class="fas fa-ethernet text-slate-600 text-3xl"></i>
      </div>
      <p class="text-slate-400 mb-4">Aucune interface trouvée</p>
      <button onclick="syncInterfaces()" class="px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl text-sm font-medium hover:opacity-90 transition">
        <i class="fas fa-sync mr-2"></i>Synchroniser depuis MikroTik
      </button>
    </div>
    @endforelse
  </div>
</div>

{{-- ===== MODAL: RENAME ===== --}}
<div id="modalRename" class="modal-backdrop hidden">
  <div class="modal-box">
    <div class="modal-hdr"><span class="modal-icon bg-blue-500/20 text-blue-400"><i class="fas fa-edit"></i></span><h3>Renommer l'interface</h3><button onclick="closeModals()" class="modal-close">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="renameId">
      <input type="hidden" id="renameOld">
      <label class="field-lbl">Nouveau nom</label>
      <input id="renameNew" type="text" class="field-inp" placeholder="WAN-Principal">
    </div>
    <div class="modal-ftr">
      <button onclick="closeModals()" class="btn-cancel">Annuler</button>
      <button onclick="confirmRename()" class="btn-save"><i class="fas fa-check mr-1"></i>Renommer</button>
    </div>
  </div>
</div>

{{-- ===== MODAL: CONFIGURE ===== --}}
<div id="modalConfig" class="modal-backdrop hidden">
  <div class="modal-box">
    <div class="modal-hdr"><span class="modal-icon bg-amber-500/20 text-amber-400"><i class="fas fa-cog"></i></span><h3>Configurer l'interface</h3><button onclick="closeModals()" class="modal-close">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="configId">
      <label class="field-lbl">MTU (64–9000)</label>
      <input id="configMtu" type="number" min="64" max="9000" class="field-inp" value="1500">
      <label class="field-lbl mt-3">L2MTU (64–9000)</label>
      <input id="configL2mtu" type="number" min="64" max="9000" class="field-inp" value="1598">
      <label class="field-lbl mt-3">Commentaire</label>
      <input id="configComment" type="text" class="field-inp" placeholder="Description...">
    </div>
    <div class="modal-ftr">
      <button onclick="closeModals()" class="btn-cancel">Annuler</button>
      <button onclick="confirmConfigure()" class="btn-save"><i class="fas fa-check mr-1"></i>Appliquer</button>
    </div>
  </div>
</div>

{{-- ===== MODAL: ASSIGN IP ===== --}}
<div id="modalIp" class="modal-backdrop hidden">
  <div class="modal-box">
    <div class="modal-hdr"><span class="modal-icon bg-cyan-500/20 text-cyan-400"><i class="fas fa-network-wired"></i></span><h3>Assigner une adresse IP</h3><button onclick="closeModals()" class="modal-close">&times;</button></div>
    <div class="modal-body">
      <input type="hidden" id="ipIfaceName">
      <label class="field-lbl">Adresse IP (CIDR) <span class="text-slate-500">ex: 192.168.1.1/24</span></label>
      <input id="ipAddress" type="text" class="field-inp font-mono" placeholder="192.168.10.1/24">
      <label class="field-lbl mt-3">Network (optionnel)</label>
      <input id="ipNetwork" type="text" class="field-inp font-mono" placeholder="192.168.10.0">
    </div>
    <div class="modal-ftr">
      <button onclick="closeModals()" class="btn-cancel">Annuler</button>
      <button onclick="confirmIp()" class="btn-save"><i class="fas fa-check mr-1"></i>Assigner</button>
    </div>
  </div>
</div>

{{-- ===== MODAL: DETAILS ===== --}}
<div id="modalDetails" class="modal-backdrop hidden">
  <div class="modal-box max-w-xl">
    <div class="modal-hdr"><span class="modal-icon bg-blue-500/20 text-blue-400"><i class="fas fa-info-circle"></i></span><h3 id="detailsTitle">Détails</h3><button onclick="closeModals()" class="modal-close">&times;</button></div>
    <div class="modal-body" id="detailsContent"><div class="text-center py-8 text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</div></div>
    <div class="modal-ftr"><button onclick="closeModals()" class="btn-cancel w-full">Fermer</button></div>
  </div>
</div>

<style>
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:3000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
.modal-backdrop.hidden{display:none!important;}
.modal-box{background:#1e293b;border:1px solid #334155;border-radius:1.25rem;width:90%;max-width:480px;max-height:90vh;overflow-y:auto;animation:popIn .2s ease;}
@keyframes popIn{from{transform:scale(.95);opacity:0}to{transform:scale(1);opacity:1}}
.modal-hdr{display:flex;align-items:center;gap:.75rem;padding:1.25rem 1.5rem;border-bottom:1px solid #334155;}
.modal-hdr h3{flex:1;font-weight:600;color:#fff;margin:0;}
.modal-icon{width:2.25rem;height:2.25rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;}
.modal-close{background:none;border:none;color:#94a3b8;font-size:1.5rem;cursor:pointer;line-height:1;}
.modal-close:hover{color:#fff;}
.modal-body{padding:1.25rem 1.5rem;}
.modal-ftr{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #334155;}
.field-lbl{display:block;font-size:.8rem;color:#94a3b8;margin-bottom:.35rem;}
.field-inp{width:100%;background:#0f172a;border:1px solid #334155;border-radius:.6rem;padding:.7rem 1rem;color:#fff;font-size:.9rem;outline:none;transition:border-color .2s;}
.field-inp:focus{border-color:rgba(6,182,212,.5);box-shadow:0 0 0 3px rgba(6,182,212,.1);}
.btn-cancel{padding:.6rem 1.25rem;background:#334155;border:none;border-radius:.6rem;color:#94a3b8;cursor:pointer;font-size:.875rem;transition:background .2s;}
.btn-cancel:hover{background:#475569;color:#fff;}
.btn-save{padding:.6rem 1.25rem;background:linear-gradient(135deg,#0ea5e9,#3b82f6);border:none;border-radius:.6rem;color:#fff;cursor:pointer;font-size:.875rem;font-weight:500;transition:opacity .2s;}
.btn-save:hover{opacity:.85;}
.detail-row{display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:.875rem;}
.detail-row:last-child{border-bottom:none;}
.detail-lbl{color:#94a3b8;}
.detail-val{color:#f1f5f9;font-weight:500;text-align:right;}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const ROUTEUR_ID = {{ $routeur->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
const IS_ONLINE = {{ $routeur->statut === 'en_ligne' ? 'true' : 'false' }};
const BASE_URL = '{{ url('') }}';

// ===== TOAST =====
function toast(msg, type = 'success') {
  const t = document.getElementById('toast');
  const el = document.createElement('div');
  const color = type === 'success' ? 'border-emerald-500 text-emerald-400' : type === 'error' ? 'border-rose-500 text-rose-400' : 'border-cyan-500 text-cyan-400';
  el.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 bg-slate-800 border rounded-xl shadow-xl text-sm transition-all duration-300 ${color}`;
  el.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'}"></i><span>${msg}</span>`;
  t.appendChild(el);
  setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }, 3500);
}

// ===== SYNC =====
async function syncInterfaces() {
  const btn = document.getElementById('syncBtn');
  const icon = document.getElementById('syncIcon');
  btn.disabled = true;
  icon.className = 'fas fa-spinner fa-spin';
  try {
    const r = await fetch(`${BASE_URL}/routeurs/${ROUTEUR_ID}/interfaces/sync`, {
      method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    });
    const d = await r.json();
    if (d.success) { toast(d.message, 'success'); setTimeout(() => location.reload(), 1200); }
    else throw new Error(d.message);
  } catch(e) { toast('Erreur : ' + e.message, 'error'); }
  finally { btn.disabled = false; icon.className = 'fas fa-sync'; }
}

// ===== TOGGLE =====
async function toggleIface(apiId, name, enable) {
  if (!confirm(`${enable ? 'Activer' : 'Désactiver'} l'interface ${name} ?`)) return;
  try {
    const r = await fetch(`${BASE_URL}/routeurs/${ROUTEUR_ID}/interfaces/${apiId}/${enable ? 'enable' : 'disable'}`, {
      method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ name })
    });
    const d = await r.json();
    if (d.success) { toast(d.message, 'success'); setTimeout(() => location.reload(), 900); }
    else toast('Erreur : ' + d.message, 'error');
  } catch(e) { toast('Erreur : ' + e.message, 'error'); }
}

// ===== MODALS =====
function closeModals() {
  document.querySelectorAll('.modal-backdrop').forEach(m => m.classList.add('hidden'));
  document.body.style.overflow = '';
}
function openModal(id) {
  document.getElementById(id).classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', e => { if(e.target === m) closeModals(); }));

// Rename
function openRename(id, name) {
  document.getElementById('renameId').value = id;
  document.getElementById('renameOld').value = name;
  document.getElementById('renameNew').value = name;
  openModal('modalRename');
  setTimeout(() => document.getElementById('renameNew').focus(), 100);
}
async function confirmRename() {
  const id = document.getElementById('renameId').value;
  const old = document.getElementById('renameOld').value;
  const nw = document.getElementById('renameNew').value.trim();
  if (!nw) { toast('Nom invalide', 'error'); return; }
  try {
    const r = await fetch(`${BASE_URL}/routeurs/${ROUTEUR_ID}/interfaces/${id}/rename`, {
      method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ name: nw, old_name: old })
    });
    const d = await r.json();
    if (d.success) { closeModals(); toast(d.message, 'success'); setTimeout(() => location.reload(), 900); }
    else toast('Erreur : ' + d.message, 'error');
  } catch(e) { toast('Erreur : ' + e.message, 'error'); }
}

// Configure
function openConfigure(id, name) {
  document.getElementById('configId').value = id;
  openModal('modalConfig');
}
async function confirmConfigure() {
  const id = document.getElementById('configId').value;
  const mtu = document.getElementById('configMtu').value;
  const l2mtu = document.getElementById('configL2mtu').value;
  const comment = document.getElementById('configComment').value;
  try {
    const r = await fetch(`${BASE_URL}/routeurs/${ROUTEUR_ID}/interfaces/${id}/configure`, {
      method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ mtu, l2mtu, comment })
    });
    const d = await r.json();
    if (d.success) { closeModals(); toast(d.message, 'success'); }
    else toast('Erreur : ' + d.message, 'error');
  } catch(e) { toast('Erreur : ' + e.message, 'error'); }
}

// Assign IP
function openAssignIp(name) {
  document.getElementById('ipIfaceName').value = name;
  document.getElementById('ipAddress').value = '';
  document.getElementById('ipNetwork').value = '';
  openModal('modalIp');
  setTimeout(() => document.getElementById('ipAddress').focus(), 100);
}
async function confirmIp() {
  const name = document.getElementById('ipIfaceName').value;
  const ip = document.getElementById('ipAddress').value.trim();
  const network = document.getElementById('ipNetwork').value.trim();
  if (!ip.match(/^\d+\.\d+\.\d+\.\d+\/\d+$/)) { toast('Format invalide — utilisez 192.168.1.1/24', 'error'); return; }
  try {
    const r = await fetch(`${BASE_URL}/routeurs/${ROUTEUR_ID}/interfaces/${encodeURIComponent(name)}/ip`, {
      method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ ip, network: network || undefined })
    });
    const d = await r.json();
    if (d.success) { closeModals(); toast(d.message, 'success'); setTimeout(() => location.reload(), 900); }
    else toast('Erreur : ' + d.message, 'error');
  } catch(e) { toast('Erreur : ' + e.message, 'error'); }
}

// Details
async function openDetails(apiId, name) {
  document.getElementById('detailsTitle').textContent = 'Détails — ' + name;
  document.getElementById('detailsContent').innerHTML = '<div class="text-center py-8 text-slate-400"><i class="fas fa-spinner fa-spin mr-2"></i>Chargement...</div>';
  openModal('modalDetails');
  try {
    const r = await fetch(`${BASE_URL}/routeurs/${ROUTEUR_ID}/interfaces/${apiId}/details`, { headers: { 'Accept': 'application/json' } });
    const d = await r.json();
    if (d.success) {
      const i = d.interface;
      const ips = (i.addresses || []).map(a => `<span class="bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded text-xs font-mono">${a.address}</span>`).join(' ') || '—';
      const rows = [
        ['ID MikroTik', i.id || '—'],
        ['Nom', i.name || '—'],
        ['Type', i.type || '—'],
        ['MAC', `<span class="font-mono text-cyan-400">${i.mac_address || '—'}</span>`],
        ['MTU', i.mtu || '—'],
        ['L2MTU', i.l2mtu || '—'],
        ['Statut', i.running ? '<span class="text-emerald-400">Actif</span>' : '<span class="text-rose-400">Inactif</span>'],
        ['Désactivé', i.disabled ? '<span class="text-rose-400">Oui</span>' : '<span class="text-emerald-400">Non</span>'],
        ['RX Bytes', Number(i.rx_byte||0).toLocaleString()],
        ['TX Bytes', Number(i.tx_byte||0).toLocaleString()],
        ['RX Erreurs', i.rx_errors || 0],
        ['TX Erreurs', i.tx_errors || 0],
        ['Commentaire', i.comment || '—'],
        ['Adresses IP', ips],
      ];
      document.getElementById('detailsContent').innerHTML =
        '<div class="divide-y divide-slate-700/50">' +
        rows.map(([l,v]) => `<div class="detail-row"><span class="detail-lbl">${l}</span><span class="detail-val">${v}</span></div>`).join('') +
        '</div>';
    } else {
      document.getElementById('detailsContent').innerHTML = `<div class="text-rose-400 text-sm py-4">${d.message}</div>`;
    }
  } catch(e) {
    document.getElementById('detailsContent').innerHTML = `<div class="text-rose-400 text-sm py-4">Erreur : ${e.message}</div>`;
  }
}

// ===== SPARKLINES (Chart.js) =====
const charts = {};
const historyMax = 20;

function initCharts() {
  document.querySelectorAll('.interface-card').forEach(card => {
    const canvas = card.querySelector('.iface-chart');
    const name = card.dataset.name;
    if (!canvas || !name) return;
    const ctx = canvas.getContext('2d');
    const data = { labels: Array(historyMax).fill(''), datasets: [
      { label: 'RX', data: Array(historyMax).fill(0), borderColor: '#06b6d4', backgroundColor: 'rgba(6,182,212,.08)', borderWidth: 1.5, fill: true, tension: 0.4, pointRadius: 0 },
      { label: 'TX', data: Array(historyMax).fill(0), borderColor: '#a855f7', backgroundColor: 'rgba(168,85,247,.08)', borderWidth: 1.5, fill: true, tension: 0.4, pointRadius: 0 },
    ]};
    charts[name] = { chart: new Chart(ctx, {
      type: 'line', data,
      options: {
        animation: false, responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false, beginAtZero: true } },
        elements: { line: { borderCapStyle: 'round' } }
      }
    }), prevRx: 0, prevTx: 0 };
  });
}

async function pollAllInterfaces() {
  if (!IS_ONLINE) return;
  const cards = document.querySelectorAll('.interface-card');
  for (const card of cards) {
    const name = card.dataset.name;
    if (!name || !charts[name]) continue;
    try {
      const r = await fetch(`${BASE_URL}/routeurs/${ROUTEUR_ID}/interfaces/${encodeURIComponent(name)}/realtime`, { headers: { 'Accept': 'application/json' } });
      const d = await r.json();
      if (!d.success) continue;

      const state = charts[name];
      const rxDelta = Math.max(0, d.rx_bytes - (state.prevRx || d.rx_bytes));
      const txDelta = Math.max(0, d.tx_bytes - (state.prevTx || d.tx_bytes));
      state.prevRx = d.rx_bytes;
      state.prevTx = d.tx_bytes;

      const rxMbps = (rxDelta * 8 / 1048576 / 5).toFixed(3); // over 5s interval
      const txMbps = (txDelta * 8 / 1048576 / 5).toFixed(3);

      const ch = state.chart;
      ch.data.datasets[0].data.push(parseFloat(rxMbps));
      ch.data.datasets[0].data.shift();
      ch.data.datasets[1].data.push(parseFloat(txMbps));
      ch.data.datasets[1].data.shift();
      ch.update('none');

      const bpsEl = card.querySelector('.iface-live-bps');
      if (bpsEl) bpsEl.textContent = `↓ ${rxMbps} / ↑ ${txMbps} Mbps`;

      const rxTxEl = card.querySelector('.iface-rx-tx');
      if (rxTxEl) rxTxEl.textContent = `${fmtBytes(d.rx_bytes)} / ${fmtBytes(d.tx_bytes)}`;

    } catch(e) { /* ignore individual errors */ }
  }
}

function fmtBytes(b) {
  b = parseFloat(b || 0);
  if (b <= 0) return '0 B';
  const u = ['B','KB','MB','GB','TB'];
  const i = Math.min(Math.floor(Math.log(b) / Math.log(1024)), 4);
  return (b / Math.pow(1024, i)).toFixed(2) + ' ' + u[i];
}

document.addEventListener('DOMContentLoaded', () => {
  initCharts();
  if (IS_ONLINE) {
    pollAllInterfaces();
    setInterval(pollAllInterfaces, 5000);
  }
});
</script>
@endsection
