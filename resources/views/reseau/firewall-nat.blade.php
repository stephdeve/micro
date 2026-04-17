<!-- NAT Rules -->
<div class="space-y-4">
    @forelse($natRules as $index => $rule)
        @php
            $chainColors = [
                'srcnat' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                'dstnat' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
            ];
            $chainColor = $chainColors[$rule['chain']] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
            $actionColors = [
                'masquerade' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                'src-nat' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                'dst-nat' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                'redirect' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
            ];
            $actionColor = $actionColors[$rule['action']] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
        @endphp
        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 border border-slate-700/50 hover:border-emerald-500/50 transition-all {{ $rule['disabled'] ? 'opacity-50' : '' }}" data-id="{{ $rule['id'] }}">
            <span class="w-6 h-6 flex items-center justify-center rounded bg-emerald-500/20 text-emerald-400 text-xs font-semibold">{{ $index + 1 }}</span>
            
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $chainColor }}">{{ $rule['chain'] }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $actionColor }}">{{ $rule['action'] }}</span>
                    @if($rule['protocol'])
                        <span class="px-2 py-0.5 rounded text-xs bg-slate-700 text-slate-300">{{ $rule['protocol'] }}</span>
                    @endif
                    @if($rule['disabled'])
                        <span class="px-2 py-0.5 rounded text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30">Désactivé</span>
                    @endif
                    
                    <span class="ml-auto flex items-center gap-2 text-xs">
                        <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-400" title="Packets">
                            <i class="fas fa-cube mr-1"></i>{{ number_format($rule['packets'] ?? 0) }}
                        </span>
                        <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-400" title="Bytes">
                            <i class="fas fa-database mr-1"></i>{{ number_format($rule['bytes'] ?? 0) }}
                        </span>
                    </span>
                </div>
                
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    @if($rule['src_address'])
                        <span class="text-slate-400">
                            <i class="fas fa-arrow-right text-emerald-400 mr-1"></i>{{ $rule['src_address'] }}{{ $rule['src_port'] ? ':' . $rule['src_port'] : '' }}
                        </span>
                    @endif
                    @if($rule['dst_address'])
                        <span class="text-slate-400">
                            <i class="fas fa-arrow-left text-cyan-400 mr-1"></i>{{ $rule['dst_address'] }}{{ $rule['dst_port'] ? ':' . $rule['dst_port'] : '' }}
                        </span>
                    @endif
                    @if($rule['to_addresses'] || $rule['to_ports'])
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-xs">
                            <i class="fas fa-map-marker-alt mr-1"></i>→ {{ $rule['to_addresses'] ?? '' }}{{ $rule['to_ports'] ? ':' . $rule['to_ports'] : '' }}
                        </span>
                    @endif
                </div>
                
                @if($rule['comment'])
                    <div class="mt-1 text-xs text-slate-500 italic">
                        <i class="fas fa-comment-alt mr-1"></i>{{ $rule['comment'] }}
                    </div>
                @endif
            </div>
            
            <div class="flex items-center gap-1">
                <div class="flex flex-col gap-0.5 mr-1">
                    <button onclick="moveNatRule('{{ $rule['id'] }}', 'up')" 
                            class="w-6 h-5 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === 0 ? 'invisible' : '' }}"
                            title="Monter">
                        <i class="fas fa-chevron-up text-xs"></i>
                    </button>
                    <button onclick="moveNatRule('{{ $rule['id'] }}', 'down')" 
                            class="w-6 h-5 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === count($natRules) - 1 ? 'invisible' : '' }}"
                            title="Descendre">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                </div>
                
                <button onclick="toggleNatRule('{{ $rule['id'] }}', {{ $rule['disabled'] ? 'true' : 'false' }})" 
                        class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-{{ $rule['disabled'] ? 'emerald' : 'amber' }}-400 transition-all"
                        title="{{ $rule['disabled'] ? 'Activer' : 'Désactiver' }}">
                    <i class="fas {{ $rule['disabled'] ? 'fa-play' : 'fa-pause' }}"></i>
                </button>
                <button onclick="deleteNatRule('{{ $rule['id'] }}')" 
                        class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-rose-400 transition-all"
                        title="Supprimer">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-slate-500">
            <i class="fas fa-exchange-alt text-2xl mb-2 opacity-50"></i>
            <p>Aucune règle NAT configurée</p>
        </div>
    @endforelse
</div>

<script>
window.toggleNatRule = async function(id, enable) {
    if (!confirm(`${enable ? 'Activer' : 'Désactiver'} cette règle NAT ?`)) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/nat/${id}/${enable ? 'enable' : 'disable'}`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur: ' + e.message, 'error');
    }
};

window.deleteNatRule = async function(id) {
    if (!confirm('Supprimer définitivement cette règle NAT ?')) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/nat/${id}`, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        const data = await response.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur: ' + e.message, 'error');
    }
};

window.moveNatRule = async function(id, direction) {
    const card = document.querySelector(`[data-id="${id}"]`);
    const allCards = document.querySelectorAll('[data-id]');
    const currentIndex = Array.from(allCards).indexOf(card);
    
    let destinationId;
    if (direction === 'up' && currentIndex > 0) {
        destinationId = allCards[currentIndex - 1].dataset.id;
    } else if (direction === 'down' && currentIndex < allCards.length - 1) {
        destinationId = allCards[currentIndex + 1].dataset.id;
    }
    
    if (!destinationId) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/nat/${id}/move`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ destination: destinationId })
        });
        
        const data = await response.json();
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 300);
        } else {
            showToast(data.message || 'Erreur', 'error');
        }
    } catch (e) {
        showToast('Erreur: ' + e.message, 'error');
    }
};
</script>
