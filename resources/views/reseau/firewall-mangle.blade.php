<!-- Mangle Rules -->
<div class="space-y-4">
    @forelse($mangleRules as $index => $rule)
        @php
            $chainGradients = [
                'prerouting' => 'from-indigo-500 to-purple-600',
                'postrouting' => 'from-cyan-500 to-blue-600',
                'forward' => 'from-emerald-500 to-teal-600',
                'input' => 'from-amber-500 to-orange-600',
                'output' => 'from-rose-500 to-pink-600',
            ];
            $chainGradient = $chainGradients[$rule['chain']] ?? 'from-slate-500 to-slate-600';
            $actionGradients = [
                'mark-routing' => 'from-indigo-500 to-purple-600',
                'mark-connection' => 'from-cyan-500 to-blue-600',
                'mark-packet' => 'from-emerald-500 to-teal-600',
                'accept' => 'from-slate-500 to-slate-600',
                'drop' => 'from-rose-500 to-red-600',
            ];
            $actionGradient = $actionGradients[$rule['action']] ?? 'from-slate-500 to-slate-600';
        @endphp
        <div class="group relative flex items-center gap-4 p-4 rounded-xl bg-slate-800/40 border border-slate-700/40 hover:border-amber-500/50 hover:bg-slate-800/60 transition-all duration-300 {{ $rule['disabled'] ? 'opacity-60' : '' }}" data-id="{{ $rule['id'] }}">
            <!-- Left Accent -->
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-0 rounded-full bg-gradient-to-b {{ $chainGradient }} group-hover:h-3/4 transition-all duration-300"></div>

            <!-- Rule Number -->
            <div class="flex flex-col items-center gap-1">
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white text-sm font-bold shadow-lg shadow-amber-500/20">{{ $index + 1 }}</span>
                <div class="flex flex-col gap-0.5">
                    <button type="button" onclick="moveMangleRule('{{ $rule['id'] }}', 'up')"
                            class="w-5 h-4 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === 0 ? 'invisible' : '' }}"
                            title="Monter">
                        <i class="fas fa-chevron-up text-[10px]"></i>
                    </button>
                    <button type="button" onclick="moveMangleRule('{{ $rule['id'] }}', 'down')"
                            class="w-5 h-4 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === count($mangleRules) - 1 ? 'invisible' : '' }}"
                            title="Descendre">
                        <i class="fas fa-chevron-down text-[10px]"></i>
                    </button>
                </div>
            </div>
            
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r {{ $chainGradient }} text-white shadow-lg">
                        {{ strtoupper($rule['chain']) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r {{ $actionGradient }} text-white shadow-lg">
                        {{ strtoupper($rule['action']) }}
                    </span>
                    @if($rule['protocol'])
                        <span class="px-2.5 py-1 rounded-full text-xs bg-slate-700/80 text-slate-300 border border-slate-600/50 font-medium">{{ strtoupper($rule['protocol']) }}</span>
                    @endif
                    @if($rule['passthrough'])
                        <span class="px-2.5 py-1 rounded-full text-xs bg-blue-500/20 text-blue-400 border border-blue-500/40 font-medium">
                            <i class="fas fa-forward mr-1"></i>passthrough
                        </span>
                    @endif
                    @if($rule['disabled'])
                        <span class="px-2.5 py-1 rounded-full text-xs bg-amber-500/20 text-amber-400 border border-amber-500/40 font-medium">
                            <i class="fas fa-pause mr-1"></i>Désactivé
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    @if($rule['src_address'])
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <i class="fas fa-arrow-right text-emerald-400 text-xs"></i>
                            <span class="text-slate-200 font-medium">{{ $rule['src_address'] }}</span>
                            @if($rule['src_port'])
                                <span class="text-emerald-400/80">:{{ $rule['src_port'] }}</span>
                            @endif
                        </div>
                    @endif
                    @if($rule['dst_address'])
                        <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-cyan-500/10 border border-cyan-500/20">
                            <i class="fas fa-arrow-left text-cyan-400 text-xs"></i>
                            <span class="text-slate-200 font-medium">{{ $rule['dst_address'] }}</span>
                            @if($rule['dst_port'])
                                <span class="text-cyan-400/80">:{{ $rule['dst_port'] }}</span>
                            @endif
                        </div>
                    @endif
                    @if($rule['new_routing_mark'])
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-500/10 border border-indigo-500/20">
                            <i class="fas fa-route text-indigo-400 text-xs"></i>
                            <span class="text-indigo-300 text-xs font-medium">{{ $rule['new_routing_mark'] }}</span>
                        </div>
                    @endif
                    @if($rule['new_connection_mark'])
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-cyan-500/10 border border-cyan-500/20">
                            <i class="fas fa-link text-cyan-400 text-xs"></i>
                            <span class="text-cyan-300 text-xs font-medium">{{ $rule['new_connection_mark'] }}</span>
                        </div>
                    @endif
                    @if($rule['new_packet_mark'])
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <i class="fas fa-tag text-emerald-400 text-xs"></i>
                            <span class="text-emerald-300 text-xs font-medium">{{ $rule['new_packet_mark'] }}</span>
                        </div>
                    @endif
                </div>

                @if($rule['comment'])
                    <div class="mt-2 flex items-center gap-2 text-xs text-slate-400 bg-slate-900/30 px-3 py-1.5 rounded-lg inline-flex">
                        <i class="fas fa-comment-alt text-slate-500"></i>
                        <span>{{ $rule['comment'] }}</span>
                    </div>
                @endif
            </div>

            <!-- Counters -->
            <div class="flex flex-col gap-1.5">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20" title="Packets">
                    <i class="fas fa-cube text-indigo-400 text-xs"></i>
                    <span class="text-xs font-bold text-indigo-300">{{ number_format($rule['packets'] ?? 0) }}</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-500/10 border border-purple-500/20" title="Bytes">
                    <i class="fas fa-database text-purple-400 text-xs"></i>
                    <span class="text-xs font-bold text-purple-300">{{ number_format($rule['bytes'] ?? 0) }}</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-1">
                <button type="button" onclick="toggleMangleRule('{{ $rule['id'] }}', {{ $rule['disabled'] ? 'true' : 'false' }})"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-700 text-slate-400 hover:text-{{ $rule['disabled'] ? 'emerald' : 'amber' }}-400 transition-all"
                        title="{{ $rule['disabled'] ? 'Activer' : 'Désactiver' }}">
                    <i class="fas {{ $rule['disabled'] ? 'fa-play' : 'fa-pause' }}"></i>
                </button>
                <button type="button" onclick="deleteMangleRule('{{ $rule['id'] }}')"
                        class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-all"
                        title="Supprimer">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>
    @empty
        <div class="text-center py-12 text-slate-500">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-800/50 border border-slate-700/50 flex items-center justify-center">
                <i class="fas fa-tags text-3xl text-slate-600"></i>
            </div>
            <p class="text-sm">Aucune règle Mangle configurée</p>
            <button type="button" onclick="openMangleModal()" class="mt-3 px-4 py-2 rounded-lg bg-amber-500/20 text-amber-400 text-sm font-medium hover:bg-amber-500/30 transition-all">
                <i class="fas fa-plus mr-2"></i>Créer une règle Mangle
            </button>
        </div>
    @endforelse
</div>

<script>
window.toggleMangleRule = async function(id, enable) {
    if (!confirm(`${enable ? 'Activer' : 'Désactiver'} cette règle Mangle ?`)) return;
    
    try {
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/mangle/${encodeURIComponent(id)}/${enable ? 'enable' : 'disable'}`, {
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
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/mangle/${encodeURIComponent(id)}`, {
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
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/mangle/${encodeURIComponent(id)}/move`, {
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
