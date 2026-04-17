@extends('layouts.app')

@section('title', 'Routes — ' . $routeur->nom)

@php
$total    = count($routes);
$actives  = count(array_filter($routes, fn($r) => ($r['active'] ?? false) && !($r['disabled'] ?? false)));
$disabled = count(array_filter($routes, fn($r) => $r['disabled'] ?? false));
$dynamic  = count(array_filter($routes, fn($r) => $r['dynamic'] ?? false));
$default  = collect($routes)->firstWhere('dst_address', '0.0.0.0/0');
@endphp

@section('content')
<div class="min-h-screen bg-slate-900 text-white py-6 pl-20 pr-4">

  {{-- Toast --}}
  <div id="toast" class="fixed top-6 right-6 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

  {{-- ===== HEADER ===== --}}
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-cyan-500/30">
        <i class="fas fa-route text-white text-xl"></i>
      </div>
      <div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent">Table de routage</h1>
        <p class="text-slate-400 text-sm">{{ $routeur->nom }} — {{ $routeur->adresse_ip }}</p>
      </div>
    </div>
    <div class="flex items-center gap-3 flex-wrap">
      <span class="px-3 py-1 text-xs rounded-full {{ $routeur->statut === 'en_ligne' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
        <span class="inline-block w-2 h-2 rounded-full {{ $routeur->statut === 'en_ligne' ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400' }} mr-1"></span>
        {{ $routeur->statut === 'en_ligne' ? 'En ligne' : 'Hors ligne' }}
      </span>
      <button id="syncBtn" onclick="syncRoutes()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-cyan-500/50 rounded-xl text-sm transition flex items-center gap-2">
        <i class="fas fa-sync" id="syncIcon"></i> Synchroniser
      </button>
      <button onclick="openAddModal()" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 hover:opacity-90 rounded-xl text-sm font-medium transition flex items-center gap-2 shadow-lg shadow-emerald-500/20">
        <i class="fas fa-plus"></i> Ajouter route
      </button>
      <a href="{{ route('routeurs.show', $routeur) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-sm transition flex items-center gap-2">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
    </div>
  </div>

  {{-- ===== DEFAULT ROUTE BANNER ===== --}}
  @if($default && !($default['disabled'] ?? false))
  <div class="mb-6 p-4 bg-gradient-to-r from-cyan-500/10 to-blue-600/5 border border-cyan-500/30 rounded-2xl flex items-center gap-4">
    <div class="w-10 h-10 bg-cyan-500/20 rounded-xl flex items-center justify-center shrink-0">
      <i class="fas fa-globe text-cyan-400"></i>
    </div>
    <div>
      <p class="text-sm font-semibold text-cyan-400">Route par défaut (Internet)</p>
      <p class="text-xs text-slate-400 font-mono">0.0.0.0/0 → {{ $default['gateway'] ?? '—' }} <span class="text-slate-500 ml-2">distance {{ $default['distance'] ?? '1' }}</span></p>
    </div>
    <span class="ml-auto px-3 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded-full border border-emerald-500/30">
      <i class="fas fa-check-circle mr-1"></i>Active
    </span>
  </div>
  @elseif(!$default)
  <div class="mb-6 p-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl flex items-center gap-3">
    <i class="fas fa-exclamation-triangle text-amber-400"></i>
    <span class="text-amber-300 text-sm">Aucune route par défaut configurée — l'accès Internet peut être impossible.</span>
  </div>
  @endif

  {{-- ===== STATS CARDS ===== --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    @foreach([
      ['Total','fas fa-list', $total, 'routes', 'blue'],
      ['Actives','fas fa-check-circle', $actives, 'fonctionnelles', 'emerald'],
      ['Désactivées','fas fa-pause-circle', $disabled, 'suspendues', 'amber'],
      ['Dynamiques','fas fa-bolt', $dynamic, 'auto-apprises', 'cyan'],
    ] as [$lb,$ic,$val,$sub,$col])
    <div class="bg-gradient-to-br from-{{ $col }}-500/10 to-{{ $col }}-600/5 border border-{{ $col }}-500/20 rounded-2xl p-5 relative overflow-hidden group hover:border-{{ $col }}-500/40 transition">
      <div class="absolute top-0 right-0 w-24 h-24 bg-{{ $col }}-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl"></div>
      <div class="relative flex items-start gap-3">
        <div class="w-10 h-10 bg-{{ $col }}-500/20 rounded-xl flex items-center justify-center shrink-0">
          <i class="{{ $ic }} text-{{ $col }}-400"></i>
        </div>
        <div>
          <div class="text-2xl font-bold text-white">{{ $val }}</div>
          <div class="text-{{ $col }}-400/70 text-xs mt-0.5">{{ $lb }} — {{ $sub }}</div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- ===== ROUTES TABLE ===== --}}
  <div class="bg-slate-800/50 border border-slate-700 rounded-2xl overflow-hidden shadow-xl mb-8">
    <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
      <h3 class="font-semibold text-white flex items-center gap-2">
        <i class="fas fa-table text-cyan-400"></i> Routes configurées
        <span class="px-2 py-0.5 bg-slate-700 text-slate-300 text-xs rounded-full ml-1">{{ $total }}</span>
      </h3>
      <div class="flex gap-2 text-xs">
        <span class="px-2 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-lg">● Actif</span>
        <span class="px-2 py-1 bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 rounded-lg">⚡ Dynamique</span>
        <span class="px-2 py-1 bg-blue-500/10 text-blue-400 border border-blue-500/20 rounded-lg">◆ Statique</span>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-900/50 border-b border-slate-700">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Destination</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Passerelle</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Distance</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Type</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Statut</th>
            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Commentaire</th>
            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-700">
          @forelse($routes as $route)
          @php
            $isDefault = ($route['dst_address'] ?? '') === '0.0.0.0/0';
            $isDynamic = $route['dynamic'] ?? false;
            $isStatic  = $route['static'] ?? false;
            $isDisabled = $route['disabled'] ?? false;
            $isActive   = ($route['active'] ?? false) && !$isDisabled;
            $isConnect  = $route['connect'] ?? false;
          @endphp
          <tr class="transition-colors hover:bg-slate-700/30 {{ $isDisabled ? 'opacity-60' : '' }} {{ $isDefault ? 'bg-cyan-500/5' : '' }}">
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-2">
                @if($isDefault)
                  <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse shrink-0"></span>
                @endif
                <code class="px-2 py-0.5 bg-slate-900 rounded-lg text-cyan-400 font-mono text-sm">{{ $route['dst_address'] ?? '—' }}</code>
                @if($isDefault)
                  <span class="text-xs text-cyan-500">(défaut)</span>
                @endif
              </div>
            </td>
            <td class="px-4 py-3.5 text-slate-300 font-mono text-sm">{{ $route['gateway'] ?? '—' }}</td>
            <td class="px-4 py-3.5">
              <span class="px-2 py-0.5 bg-slate-700 text-slate-300 text-xs rounded-lg">{{ $route['distance'] ?? '1' }}</span>
            </td>
            <td class="px-4 py-3.5">
              @if($isDynamic)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-cyan-500/15 text-cyan-400 text-xs border border-cyan-500/20"><i class="fas fa-bolt"></i> Dynamique</span>
              @elseif($isConnect)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-500/15 text-purple-400 text-xs border border-purple-500/20"><i class="fas fa-plug"></i> Connecté</span>
              @elseif($isStatic)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-500/15 text-blue-400 text-xs border border-blue-500/20"><i class="fas fa-cube"></i> Statique</span>
              @else
                <span class="text-slate-500 text-xs">—</span>
              @endif
            </td>
            <td class="px-4 py-3.5">
              @if($isDisabled)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-400 text-xs border border-amber-500/20"><i class="fas fa-pause-circle"></i> Désactivé</span>
              @elseif($isActive)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 text-xs border border-emerald-500/20"><i class="fas fa-check-circle"></i> Actif</span>
              @else
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-700 text-slate-400 text-xs"><i class="fas fa-circle"></i> Inactif</span>
              @endif
            </td>
            <td class="px-4 py-3.5 text-slate-400 text-sm max-w-[160px] truncate">{{ $route['comment'] ?: '—' }}</td>
            <td class="px-4 py-3.5">
              <div class="flex items-center justify-center gap-1.5">
                @if(!$isDynamic && !$isConnect)
                  @if($isDisabled)
                    <button onclick="toggleRoute('{{ $route['id'] ?? '' }}', true)" title="Activer"
                      class="w-8 h-8 rounded-lg bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 transition flex items-center justify-center">
                      <i class="fas fa-play text-xs"></i>
                    </button>
                  @else
                    <button onclick="toggleRoute('{{ $route['id'] ?? '' }}', false)" title="Désactiver"
                      class="w-8 h-8 rounded-lg bg-amber-500/20 hover:bg-amber-500/30 text-amber-400 transition flex items-center justify-center">
                      <i class="fas fa-pause text-xs"></i>
                    </button>
                  @endif
                  <button onclick="editRoute('{{ $route['id'] ?? '' }}','{{ addslashes($route['dst_address'] ?? '') }}','{{ addslashes($route['gateway'] ?? '') }}','{{ $route['distance'] ?? 1 }}','{{ addslashes($route['comment'] ?? '') }}')"
                    title="Modifier"
                    class="w-8 h-8 rounded-lg bg-blue-500/20 hover:bg-blue-500/30 text-blue-400 transition flex items-center justify-center">
                    <i class="fas fa-edit text-xs"></i>
                  </button>
                  <button onclick="deleteRoute('{{ $route['id'] ?? '' }}')" title="Supprimer"
                    class="w-8 h-8 rounded-lg bg-rose-500/20 hover:bg-rose-500/30 text-rose-400 transition flex items-center justify-center">
                    <i class="fas fa-trash text-xs"></i>
                  </button>
                @else
                  <span class="text-slate-600 text-xs px-2">Lecture seule</span>
                @endif
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="7" class="px-4 py-16 text-center">
              <div class="w-16 h-16 bg-slate-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-route text-slate-600 text-2xl"></i>
              </div>
              <p class="text-slate-400 mb-2">Aucune route configurée</p>
              <button onclick="openAddModal()" class="px-4 py-2 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-xl text-sm hover:bg-emerald-500/30 transition">
                <i class="fas fa-plus mr-2"></i>Ajouter une route statique
              </button>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- ===== LEGEND ===== --}}
  <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-5">
    <h4 class="text-sm font-semibold text-slate-300 mb-3 flex items-center gap-2"><i class="fas fa-info-circle text-cyan-400"></i> Légende & aide</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-xs text-slate-400">
      <div><span class="text-cyan-400 font-medium">Destination</span><br>Réseau cible ex: 192.168.20.0/24</div>
      <div><span class="text-cyan-400 font-medium">Passerelle</span><br>IP du prochain saut ex: 192.168.1.254</div>
      <div><span class="text-cyan-400 font-medium">Distance</span><br>Priorité (1–255, plus petit = prioritaire)</div>
      <div><span class="text-cyan-400 font-medium">Dynamique</span><br>Route apprise via OSPF, BGP, DHCP…</div>
      <div><span class="text-cyan-400 font-medium">0.0.0.0/0</span><br>Route par défaut vers Internet</div>
      <div><span class="text-cyan-400 font-medium">Lecture seule</span><br>Routes dynamiques/connectées non modifiables</div>
    </div>
  </div>

</div>

{{-- ===== MODAL ADD/EDIT ===== --}}
<div id="routeModal" class="modal-backdrop hidden">
  <div class="modal-box max-w-lg">
    <div class="modal-hdr">
      <span class="modal-icon bg-cyan-500/20 text-cyan-400"><i class="fas fa-route"></i></span>
      <h3 id="modalTitle">Ajouter une route statique</h3>
      <button onclick="closeModal()" class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="routeId">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="field-lbl">Destination (CIDR) <span class="text-rose-400">*</span></label>
          <input id="dstAddress" type="text" class="field-inp font-mono" placeholder="192.168.20.0/24">
          <p class="text-xs text-slate-500 mt-1">Format: réseau/masque</p>
        </div>
        <div>
          <label class="field-lbl">Passerelle <span class="text-rose-400">*</span></label>
          <input id="gateway" type="text" class="field-inp font-mono" placeholder="192.168.1.254">
        </div>
        <div>
          <label class="field-lbl">Distance (1–255)</label>
          <input id="distance" type="number" min="1" max="255" class="field-inp" value="1">
          <p class="text-xs text-slate-500 mt-1">1 = prioritaire</p>
        </div>
        <div>
          <label class="field-lbl">Vérification Gateway</label>
          <select id="checkGateway" class="field-inp">
            <option value="">— Aucune —</option>
            <option value="ping">Ping</option>
            <option value="arp">ARP</option>
          </select>
        </div>
      </div>
      <div class="mt-4">
        <label class="field-lbl">Commentaire</label>
        <input id="comment" type="text" class="field-inp" placeholder="Route vers réseau X">
      </div>
    </div>
    <div class="modal-ftr">
      <button onclick="closeModal()" class="btn-cancel">Annuler</button>
      <button id="saveBtn" onclick="saveRoute()" class="btn-save"><i class="fas fa-check mr-1"></i>Enregistrer</button>
    </div>
  </div>
</div>

<style>
.modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:3000;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(4px);}
.modal-backdrop.hidden{display:none!important;}
.modal-box{background:#1e293b;border:1px solid #334155;border-radius:1.25rem;width:90%;max-width:480px;max-height:90vh;overflow-y:auto;animation:popIn .2s ease;}
@keyframes popIn{from{transform:scale(.95);opacity:0}to{transform:scale(1);opacity:1}}
.modal-hdr{display:flex;align-items:center;gap:.75rem;padding:1.25rem 1.5rem;border-bottom:1px solid #334155;}
.modal-hdr h3{flex:1;font-weight:600;color:#fff;margin:0;}
.modal-icon{width:2.25rem;height:2.25rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.modal-close{background:none;border:none;color:#94a3b8;font-size:1.5rem;cursor:pointer;line-height:1;}
.modal-close:hover{color:#fff;}
.modal-body{padding:1.25rem 1.5rem;}
.modal-ftr{display:flex;justify-content:flex-end;gap:.75rem;padding:1rem 1.5rem;border-top:1px solid #334155;}
.field-lbl{display:block;font-size:.8rem;color:#94a3b8;margin-bottom:.35rem;}
.field-inp{width:100%;background:#0f172a;border:1px solid #334155;border-radius:.6rem;padding:.7rem 1rem;color:#fff;font-size:.875rem;outline:none;transition:border-color .2s;}
.field-inp:focus{border-color:rgba(6,182,212,.5);box-shadow:0 0 0 3px rgba(6,182,212,.1);}
.btn-cancel{padding:.6rem 1.25rem;background:#334155;border:none;border-radius:.6rem;color:#94a3b8;cursor:pointer;font-size:.875rem;transition:background .2s;}
.btn-cancel:hover{background:#475569;color:#fff;}
.btn-save{padding:.6rem 1.25rem;background:linear-gradient(135deg,#0ea5e9,#3b82f6);border:none;border-radius:.6rem;color:#fff;cursor:pointer;font-size:.875rem;font-weight:500;transition:opacity .2s;}
.btn-save:hover{opacity:.85;}
.btn-save:disabled{opacity:.5;cursor:not-allowed;}
</style>

<script>
const routeurId = {{ $routeur->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

function toast(msg, type = 'success') {
  const t = document.getElementById('toast');
  const el = document.createElement('div');
  const styles = {
    success: 'border-emerald-500 text-emerald-400',
    error:   'border-rose-500 text-rose-400',
    info:    'border-cyan-500 text-cyan-400',
    warning: 'border-amber-500 text-amber-400',
  };
  const icons = { success:'check-circle', error:'times-circle', info:'info-circle', warning:'exclamation-triangle' };
  el.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 bg-slate-800 border rounded-xl shadow-xl text-sm ${styles[type]||styles.info}`;
  el.innerHTML = `<i class="fas fa-${icons[type]||'info-circle'}"></i><span>${msg}</span>`;
  t.appendChild(el);
  setTimeout(()=>{ el.style.opacity='0'; el.style.transition='opacity .3s'; setTimeout(()=>el.remove(),300); }, 3500);
}

// Sync
async function syncRoutes() {
  const btn = document.getElementById('syncBtn');
  const icon = document.getElementById('syncIcon');
  btn.disabled = true; icon.className = 'fas fa-spinner fa-spin';
  try {
    const r = await fetch(`/routeurs/${routeurId}/routes/sync`, {
      method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
    });
    const d = await r.json();
    if (d.success) { toast(d.message,'success'); setTimeout(()=>location.reload(),1200); }
    else toast('Erreur : '+d.message,'error');
  } catch(e){ toast('Erreur : '+e.message,'error'); }
  finally{ btn.disabled=false; icon.className='fas fa-sync'; }
}

// Modal
function closeModal() {
  document.getElementById('routeModal').classList.add('hidden');
  document.body.style.overflow='';
}
function openAddModal() {
  document.getElementById('routeId').value='';
  document.getElementById('modalTitle').textContent='Ajouter une route statique';
  document.getElementById('dstAddress').value='';
  document.getElementById('gateway').value='';
  document.getElementById('distance').value='1';
  document.getElementById('checkGateway').value='';
  document.getElementById('comment').value='';
  document.getElementById('routeModal').classList.remove('hidden');
  document.body.style.overflow='hidden';
  setTimeout(()=>document.getElementById('dstAddress').focus(),100);
}
function editRoute(id,dst,gw,dist,cm) {
  document.getElementById('routeId').value=id;
  document.getElementById('modalTitle').textContent='Modifier la route';
  document.getElementById('dstAddress').value=dst;
  document.getElementById('gateway').value=gw;
  document.getElementById('distance').value=dist;
  document.getElementById('comment').value=cm||'';
  document.getElementById('routeModal').classList.remove('hidden');
  document.body.style.overflow='hidden';
}
document.getElementById('routeModal').addEventListener('click',e=>{if(e.target===e.currentTarget)closeModal();});

// Save
async function saveRoute() {
  const btn = document.getElementById('saveBtn');
  const id  = document.getElementById('routeId').value;
  const dst = document.getElementById('dstAddress').value.trim();
  const gw  = document.getElementById('gateway').value.trim();
  const dist= document.getElementById('distance').value;
  const cgw = document.getElementById('checkGateway').value;
  const cm  = document.getElementById('comment').value;

  if (!dst.match(/^\d+\.\d+\.\d+\.\d+\/\d+$/)) { toast('Format destination invalide — ex: 192.168.20.0/24','error'); return; }
  if (!gw.match(/^\d+\.\d+\.\d+\.\d+$/))        { toast('Passerelle invalide — ex: 192.168.1.254','error'); return; }

  btn.disabled=true; btn.innerHTML='<i class="fas fa-spinner fa-spin mr-1"></i>Enregistrement…';
  const config = {dst_address:dst, gateway:gw, distance:parseInt(dist)||1, check_gateway:cgw, comment:cm};

  try {
    const url    = id ? `/routeurs/${routeurId}/routes/${id}` : `/routeurs/${routeurId}/routes`;
    const method = id ? 'PUT' : 'POST';
    const r = await fetch(url, {
      method, headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json','Accept':'application/json'},
      body:JSON.stringify(config)
    });
    const d = await r.json();
    if (d.success) { closeModal(); toast(d.message,'success'); setTimeout(()=>location.reload(),900); }
    else toast('Erreur : '+(d.message||'Échec'),'error');
  } catch(e){ toast('Erreur : '+e.message,'error'); }
  finally{ btn.disabled=false; btn.innerHTML='<i class="fas fa-check mr-1"></i>Enregistrer'; }
}

// Toggle
async function toggleRoute(id, enable) {
  if (!confirm(`${enable?'Activer':'Désactiver'} cette route ?`)) return;
  try {
    const r = await fetch(`/routeurs/${routeurId}/routes/${id}/${enable?'enable':'disable'}`, {
      method:'POST', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
    });
    const d = await r.json();
    if (d.success) { toast(d.message,'success'); setTimeout(()=>location.reload(),900); }
    else toast('Erreur : '+d.message,'error');
  } catch(e){ toast('Erreur : '+e.message,'error'); }
}

// Delete
async function deleteRoute(id) {
  if (!confirm('Supprimer définitivement cette route ?')) return;
  try {
    const r = await fetch(`/routeurs/${routeurId}/routes/${id}`, {
      method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Accept':'application/json'}
    });
    const d = await r.json();
    if (d.success) { toast(d.message,'success'); setTimeout(()=>location.reload(),900); }
    else toast('Erreur : '+d.message,'error');
  } catch(e){ toast('Erreur : '+e.message,'error'); }
}
</script>
@endsection
