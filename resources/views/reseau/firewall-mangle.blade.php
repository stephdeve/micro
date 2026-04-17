<!-- Mangle Rules -->
<div class="space-y-4">
    @forelse($mangleRules as $index => $rule)
        @php
            $chainColors = [
                'prerouting' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                'postrouting' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
                'forward' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                'input' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                'output' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
            ];
            $chainColor = $chainColors[$rule['chain']] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
            $actionColors = [
                'mark-routing' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                'mark-connection' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
                'mark-packet' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                'accept' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
                'drop' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
            ];
            $actionColor = $actionColors[$rule['action']] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
        @endphp
        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 border border-slate-700/50 hover:border-amber-500/50 transition-all {{ $rule['disabled'] ? 'opacity-50' : '' }}" data-id="{{ $rule['id'] }}">
            <span class="w-6 h-6 flex items-center justify-center rounded bg-amber-500/20 text-amber-400 text-xs font-semibold">{{ $index + 1 }}</span>
            
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $chainColor }}">{{ $rule['chain'] }}</span>
                    <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $actionColor }}">{{ $rule['action'] }}</span>
                    @if($rule['protocol'])
                        <span class="px-2 py-0.5 rounded text-xs bg-slate-700 text-slate-300">{{ $rule['protocol'] }}</span>
                    @endif
                    @if($rule['passthrough'])
                        <span class="px-2 py-0.5 rounded text-xs bg-blue-500/20 text-blue-400 border border-blue-500/30">passthrough</span>
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
                    @if($rule['new_routing_mark'])
                        <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-400 text-xs">
                            <i class="fas fa-route mr-1"></i>{{ $rule['new_routing_mark'] }}
                        </span>
                    @endif
                    @if($rule['new_connection_mark'])
                        <span class="px-2 py-0.5 rounded bg-cyan-500/20 text-cyan-400 text-xs">
                            <i class="fas fa-link mr-1"></i>{{ $rule['new_connection_mark'] }}
                        </span>
                    @endif
                    @if($rule['new_packet_mark'])
                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-xs">
                            <i class="fas fa-tag mr-1"></i>{{ $rule['new_packet_mark'] }}
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
                    <button onclick="moveMangleRule('{{ $rule['id'] }}', 'up')" 
                            class="w-6 h-5 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === 0 ? 'invisible' : '' }}"
                            title="Monter">
                        <i class="fas fa-chevron-up text-xs"></i>
                    </button>
                    <button onclick="moveMangleRule('{{ $rule['id'] }}', 'down')" 
                            class="w-6 h-5 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === count($mangleRules) - 1 ? 'invisible' : '' }}"
                            title="Descendre">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                </div>
                
                <button onclick="toggleMangleRule('{{ $rule['id'] }}', {{ $rule['disabled'] ? 'true' : 'false' }})" 
                        class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-{{ $rule['disabled'] ? 'emerald' : 'amber' }}-400 transition-all"
                        title="{{ $rule['disabled'] ? 'Activer' : 'Désactiver' }}">
                    <i class="fas {{ $rule['disabled'] ? 'fa-play' : 'fa-pause' }}"></i>
                </button>
                <button onclick="deleteMangleRule('{{ $rule['id'] }}')" 
                        class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-rose-400 transition-all"
                        title="Supprimer">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-8 text-slate-500">
            <i class="fas fa-tags text-2xl mb-2 opacity-50"></i>
            <p>Aucune règle Mangle configurée</p>
        </div>
    @endforelse
</div>

<script>
window.toggleMangleRule = async function(id, enable) {
    if (!confirm(`${enable ? 'Activer' : 'Désactiver'} cette règle Mangle ?`)) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/mangle/${id}/${enable ? 'enable' : 'disable'}`, {
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

window.deleteMangleRule = async function(id) {
    if (!confirm('Supprimer définitivement cette règle Mangle ?')) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/mangle/${id}`, {
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

window.moveMangleRule = async function(id, direction) {
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
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/mangle/${id}/move`, {
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
