<!-- Filter Rules -->
<div class="space-y-4">
    @foreach(['INPUT' => 'input', 'FORWARD' => 'forward', 'OUTPUT' => 'output'] as $chainName => $chainKey)
    <div class="rounded-xl bg-slate-800/50 border border-slate-700/50 overflow-hidden">
        <div class="px-4 py-3 bg-slate-900/50 border-b border-slate-700/50 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-cyan-400 font-semibold">{{ $chainName }}</span>
                <span class="text-xs text-slate-500">({{ count($groupedFilters[$chainName] ?? []) }} règles)</span>
            </div>
        </div>
        
        <div class="p-3 space-y-2">
            @forelse($groupedFilters[$chainName] ?? [] as $index => $rule)
                @php
                    $actionColors = [
                        'accept' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                        'drop' => 'bg-rose-500/20 text-rose-400 border-rose-500/30',
                        'reject' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        'log' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                    ];
                    $actionColor = $actionColors[$rule['action']] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                @endphp
                <div class="group flex items-center gap-3 p-3 rounded-lg bg-slate-900/50 border border-slate-700/50 hover:border-cyan-500/50 transition-all {{ $rule['disabled'] ? 'opacity-50' : '' }}" data-id="{{ $rule['id'] }}">
                    <!-- Rule Number -->
                    <span class="w-6 h-6 flex items-center justify-center rounded bg-cyan-500/20 text-cyan-400 text-xs font-semibold">{{ $index + 1 }}</span>
                    
                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $actionColor }}">{{ $rule['action'] }}</span>
                            @if($rule['protocol'])
                                <span class="px-2 py-0.5 rounded text-xs bg-slate-700 text-slate-300">{{ $rule['protocol'] }}</span>
                            @endif
                            @if($rule['disabled'])
                                <span class="px-2 py-0.5 rounded text-xs bg-amber-500/20 text-amber-400 border border-amber-500/30">Désactivé</span>
                            @endif
                            
                            <!-- Counters -->
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
                                    <i class="fas fa-arrow-right text-emerald-400 mr-1"></i>
                                    {{ $rule['src_address'] }}{{ $rule['src_port'] ? ':' . $rule['src_port'] : '' }}
                                </span>
                            @endif
                            @if($rule['dst_address'])
                                <span class="text-slate-400">
                                    <i class="fas fa-arrow-left text-cyan-400 mr-1"></i>
                                    {{ $rule['dst_address'] }}{{ $rule['dst_port'] ? ':' . $rule['dst_port'] : '' }}
                                </span>
                            @endif
                            @if($rule['in_interface'])
                                <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-xs">
                                    <i class="fas fa-sign-in-alt mr-1"></i>{{ $rule['in_interface'] }}
                                </span>
                            @endif
                            @if($rule['out_interface'])
                                <span class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-400 text-xs">
                                    <i class="fas fa-sign-out-alt mr-1"></i>{{ $rule['out_interface'] }}
                                </span>
                            @endif
                        </div>
                        
                        @if($rule['comment'])
                            <div class="mt-1 text-xs text-slate-500 italic">
                                <i class="fas fa-comment-alt mr-1"></i>{{ $rule['comment'] }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex items-center gap-1">
                        <!-- Move Buttons -->
                        <div class="flex flex-col gap-0.5 mr-1">
                            <button onclick="moveFilterRule('{{ $rule['id'] }}', 'up')" 
                                    class="w-6 h-5 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === 0 ? 'invisible' : '' }}"
                                    title="Monter">
                                <i class="fas fa-chevron-up text-xs"></i>
                            </button>
                            <button onclick="moveFilterRule('{{ $rule['id'] }}', 'down')" 
                                    class="w-6 h-5 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === count($groupedFilters[$chainName] ?? []) - 1 ? 'invisible' : '' }}"
                                    title="Descendre">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                        </div>
                        
                        <button onclick="toggleFilterRule('{{ $rule['id'] }}', {{ $rule['disabled'] ? 'true' : 'false' }})" 
                                class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-{{ $rule['disabled'] ? 'emerald' : 'amber' }}-400 transition-all"
                                title="{{ $rule['disabled'] ? 'Activer' : 'Désactiver' }}">
                            <i class="fas {{ $rule['disabled'] ? 'fa-play' : 'fa-pause' }}"></i>
                        </button>
                        <button onclick="deleteFilterRule('{{ $rule['id'] }}')" 
                                class="w-8 h-8 flex items-center justify-center rounded hover:bg-slate-700 text-slate-400 hover:text-rose-400 transition-all"
                                title="Supprimer">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 text-sm">
                    <i class="fas fa-filter text-2xl mb-2 opacity-50"></i>
                    <p>Aucune règle dans cette chaîne</p>
                </div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<script>
window.toggleFilterRule = async function(id, enable) {
    if (!confirm(`${enable ? 'Activer' : 'Désactiver'} cette règle ?`)) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/filter/${id}/${enable ? 'enable' : 'disable'}`, {
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

window.deleteFilterRule = async function(id) {
    if (!confirm('Supprimer définitivement cette règle ?')) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/filter/${id}`, {
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

window.moveFilterRule = async function(id, direction) {
    const card = document.querySelector(`[data-id="${id}"]`);
    const chainSection = card.closest('.rounded-xl');
    const allCards = chainSection.querySelectorAll('[data-id]');
    const currentIndex = Array.from(allCards).indexOf(card);
    
    let destinationId;
    if (direction === 'up' && currentIndex > 0) {
        destinationId = allCards[currentIndex - 1].dataset.id;
    } else if (direction === 'down' && currentIndex < allCards.length - 1) {
        destinationId = allCards[currentIndex + 1].dataset.id;
    }
    
    if (!destinationId) return;
    
    try {
        const response = await fetch(`${BASE_URL}/admin-reseau/routeurs/${routeurId}/firewall/filter/${id}/move`, {
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
