<!-- Filter Rules -->
<div class="space-y-6">
    @foreach(['INPUT' => 'input', 'FORWARD' => 'forward', 'OUTPUT' => 'output'] as $chainName => $chainKey)
    @php
        $chainIcons = [
            'INPUT' => 'fa-sign-in-alt',
            'FORWARD' => 'fa-exchange-alt',
            'OUTPUT' => 'fa-sign-out-alt'
        ];
        $chainGradients = [
            'INPUT' => 'from-emerald-500/20 to-emerald-600/10 border-emerald-500/30',
            'FORWARD' => 'from-cyan-500/20 to-cyan-600/10 border-cyan-500/30',
            'OUTPUT' => 'from-violet-500/20 to-violet-600/10 border-violet-500/30'
        ];
        $chainCount = count($groupedFilters[$chainName] ?? []);
    @endphp
    <div class="rounded-2xl bg-slate-900/40 border border-slate-700/50 overflow-hidden backdrop-blur-sm hover:border-slate-600/50 transition-all duration-300">
        <div class="px-5 py-4 bg-gradient-to-r {{ $chainGradients[$chainName] }} border-b border-slate-700/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800/80 border border-slate-700/50 flex items-center justify-center shadow-lg">
                    <i class="fas {{ $chainIcons[$chainName] }} text-lg {{ $chainName === 'INPUT' ? 'text-emerald-400' : ($chainName === 'FORWARD' ? 'text-cyan-400' : 'text-violet-400') }}"></i>
                </div>
                <div>
                    <span class="text-white font-bold text-lg">{{ $chainName }}</span>
                    <span class="ml-2 text-xs text-slate-400 bg-slate-800/80 px-2 py-0.5 rounded-full border border-slate-700/50">{{ $chainCount }} règle{{ $chainCount > 1 ? 's' : '' }}</span>
                </div>
            </div>
            <button type="button" onclick="openFilterModal()" class="group px-3 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-700 border border-slate-700/50 text-slate-300 text-xs font-medium transition-all hover:scale-105">
                <i class="fas fa-plus mr-1 group-hover:rotate-90 transition-transform"></i>Ajouter
            </button>
        </div>
        
        <div class="p-4 space-y-3">
            @forelse($groupedFilters[$chainName] ?? [] as $index => $rule)
                @php
                    $actionGradients = [
                        'accept' => 'from-emerald-500 to-emerald-600 shadow-emerald-500/25',
                        'drop' => 'from-rose-500 to-rose-600 shadow-rose-500/25',
                        'reject' => 'from-amber-500 to-amber-600 shadow-amber-500/25',
                        'log' => 'from-blue-500 to-blue-600 shadow-blue-500/25',
                    ];
                    $actionGradient = $actionGradients[$rule['action']] ?? 'from-slate-500 to-slate-600';
                    $actionIcons = [
                        'accept' => 'fa-check',
                        'drop' => 'fa-ban',
                        'reject' => 'fa-times',
                        'log' => 'fa-file-alt'
                    ];
                @endphp
                <div class="group relative flex items-center gap-4 p-4 rounded-xl bg-slate-800/40 border border-slate-700/40 hover:border-cyan-500/50 hover:bg-slate-800/60 transition-all duration-300 {{ $rule['disabled'] ? 'opacity-60' : '' }}" data-id="{{ $rule['id'] }}">
                    <!-- Left Accent Bar -->
                    <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-0 rounded-full bg-gradient-to-b {{ $actionGradient }} group-hover:h-3/4 transition-all duration-300"></div>

                    <!-- Rule Number -->
                    <div class="flex flex-col items-center gap-1">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-gradient-to-br {{ $actionGradient }} text-white text-sm font-bold shadow-lg">{{ $index + 1 }}</span>
                        <div class="flex flex-col gap-0.5">
                            <button type="button" onclick="moveFilterRule('{{ $rule['id'] }}', 'up')"
                                    class="w-5 h-4 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === 0 ? 'invisible' : '' }}"
                                    title="Monter">
                                <i class="fas fa-chevron-up text-[10px]"></i>
                            </button>
                            <button type="button" onclick="moveFilterRule('{{ $rule['id'] }}', 'down')"
                                    class="w-5 h-4 flex items-center justify-center rounded hover:bg-slate-700 text-slate-500 hover:text-white transition-all {{ $index === count($groupedFilters[$chainName] ?? []) - 1 ? 'invisible' : '' }}"
                                    title="Descendre">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r {{ $actionGradient }} text-white shadow-lg">
                                <i class="fas {{ $actionIcons[$rule['action']] ?? 'fa-cog' }} mr-1"></i>{{ strtoupper($rule['action']) }}
                            </span>
                            @if($rule['protocol'])
                                <span class="px-2.5 py-1 rounded-full text-xs bg-slate-700/80 text-slate-300 border border-slate-600/50 font-medium">{{ strtoupper($rule['protocol']) }}</span>
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
                            @if($rule['in_interface'])
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-700/50 border border-slate-600/30">
                                    <i class="fas fa-sign-in-alt text-slate-400 text-xs"></i>
                                    <span class="text-slate-300 text-xs font-medium">{{ $rule['in_interface'] }}</span>
                                </div>
                            @endif
                            @if($rule['out_interface'])
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-700/50 border border-slate-600/30">
                                    <i class="fas fa-sign-out-alt text-slate-400 text-xs"></i>
                                    <span class="text-slate-300 text-xs font-medium">{{ $rule['out_interface'] }}</span>
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
                        <button type="button" onclick="toggleFilterRule('{{ $rule['id'] }}', {{ $rule['disabled'] ? 'true' : 'false' }})"
                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-700 text-slate-400 hover:text-{{ $rule['disabled'] ? 'emerald' : 'amber' }}-400 transition-all"
                                title="{{ $rule['disabled'] ? 'Activer' : 'Désactiver' }}">
                            <i class="fas {{ $rule['disabled'] ? 'fa-play' : 'fa-pause' }}"></i>
                        </button>
                        <button type="button" onclick="deleteFilterRule('{{ $rule['id'] }}')"
                                class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 transition-all"
                                title="Supprimer">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-500">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-slate-800/50 border border-slate-700/50 flex items-center justify-center">
                        <i class="fas fa-filter text-3xl text-slate-600"></i>
                    </div>
                    <p class="text-sm">Aucune règle dans cette chaîne</p>
                    <button type="button" onclick="openFilterModal()" class="mt-3 px-4 py-2 rounded-lg bg-cyan-500/20 text-cyan-400 text-sm font-medium hover:bg-cyan-500/30 transition-all">
                        <i class="fas fa-plus mr-2"></i>Créer une règle
                    </button>
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
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/filter/${encodeURIComponent(id)}/${enable ? 'enable' : 'disable'}`, {
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
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/filter/${encodeURIComponent(id)}`, {
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
        const response = await fetch(`${BASE_URL}/routeurs/${routeurId}/firewall/filter/${encodeURIComponent(id)}/move`, {
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
