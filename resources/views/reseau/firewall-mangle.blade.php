<!-- Mangle Rules Section (QoS / Packet Marking) -->
<div class="rules-section">
    <div class="section-header" style="margin-bottom: 1rem;">
        <h3><i class="fas fa-tags"></i> Mangle Rules (Marquage de paquets)</h3>
    </div>

    <div class="mangle-info">
        <div class="info-item">
            <i class="fas fa-route"></i>
            <span><strong>Marquage de routage:</strong> Diriger le trafic vers différentes routes</span>
        </div>
        <div class="info-item">
            <i class="fas fa-link"></i>
            <span><strong>Marquage de connexion:</strong> Identifier et tracker les connexions</span>
        </div>
        <div class="info-item">
            <i class="fas fa-box"></i>
            <span><strong>Marquage de paquets:</strong> Marquer individuellement pour QoS</span>
        </div>
    </div>

    <div class="rules-list" id="mangle-rules-list">
        @forelse($mangleRules as $index => $rule)
            <div class="rule-card {{ ($rule['disabled'] ?? false) ? 'disabled' : '' }}" 
                 data-id="{{ $rule['id'] ?? '' }}" 
                 draggable="true"
                 ondragstart="dragStart(event, '{{ $rule['id'] ?? '' }}')"
                 ondragover="dragOver(event)"
                 ondrop="dragDrop(event, '{{ $rule['id'] ?? '' }}')">
                
                <div class="rule-drag-handle">⋮⋮</div>
                
                <div class="rule-main">
                    <div class="rule-header">
                        <span class="rule-number">{{ $index + 1 }}</span>
                        <span class="badge badge-chain-{{ $rule['chain'] ?? 'default' }}">{{ $rule['chain'] ?? '' }}</span>
                        <span class="badge badge-action-{{ $rule['action'] ?? 'default' }}">{{ $rule['action'] ?? '' }}</span>
                        @if($rule['protocol'] ?? false)
                            <span class="badge badge-protocol">{{ $rule['protocol'] }}</span>
                        @endif
                        @if($rule['passthrough'] ?? false)
                            <span class="badge badge-info">passthrough</span>
                        @endif
                        @if($rule['disabled'] ?? false)
                            <span class="badge badge-warning">Désactivé</span>
                        @endif
                    </div>
                    
                    <div class="rule-details">
                        @if($rule['src_address'] ?? false)
                            <span class="rule-param"><i class="fas fa-arrow-right"></i> SRC: {{ $rule['src_address'] }}{{ ($rule['src_port'] ?? false) ? ':'.$rule['src_port'] : '' }}</span>
                        @endif
                        @if($rule['dst_address'] ?? false)
                            <span class="rule-param"><i class="fas fa-arrow-left"></i> DST: {{ $rule['dst_address'] }}{{ ($rule['dst_port'] ?? false) ? ':'.$rule['dst_port'] : '' }}</span>
                        @endif
                        @if($rule['new_routing_mark'] ?? false)
                            <span class="rule-param" style="background: rgba(102,126,234,0.2); color: #667eea;">
                                <i class="fas fa-route"></i> Routing Mark: {{ $rule['new_routing_mark'] }}
                            </span>
                        @endif
                        @if($rule['new_connection_mark'] ?? false)
                            <span class="rule-param" style="background: rgba(0,166,255,0.2); color: #00a6ff;">
                                <i class="fas fa-link"></i> Conn Mark: {{ $rule['new_connection_mark'] }}
                            </span>
                        @endif
                        @if($rule['new_packet_mark'] ?? false)
                            <span class="rule-param" style="background: rgba(46,247,91,0.2); color: #2ef75b;">
                                <i class="fas fa-box"></i> Packet Mark: {{ $rule['new_packet_mark'] }}
                            </span>
                        @endif
                        @if($rule['in_interface'] ?? false)
                            <span class="rule-param"><i class="fas fa-plug"></i> IN: {{ $rule['in_interface'] }}</span>
                        @endif
                        @if($rule['out_interface'] ?? false)
                            <span class="rule-param"><i class="fas fa-plug"></i> OUT: {{ $rule['out_interface'] }}</span>
                        @endif
                    </div>
                    
                    @if($rule['comment'] ?? false)
                        <div class="rule-comment">💬 {{ $rule['comment'] }}</div>
                    @endif
                </div>
                
                <div class="rule-actions">
                    <button onclick="toggleMangleRule('{{ $rule['id'] ?? '' }}', {{ ($rule['disabled'] ?? false) ? 'true' : 'false' }})" 
                            class="btn-icon {{ ($rule['disabled'] ?? false) ? 'btn-success' : 'btn-warning' }}" 
                            title="{{ ($rule['disabled'] ?? false) ? 'Activer' : 'Désactiver' }}">
                        <i class="fas {{ ($rule['disabled'] ?? false) ? 'fa-play' : 'fa-pause' }}"></i>
                    </button>
                    <button onclick="editMangleRule('{{ $rule['id'] ?? '' }}', {{ json_encode($rule) }})" 
                            class="btn-icon" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteMangleRule('{{ $rule['id'] ?? '' }}')" 
                            class="btn-icon btn-danger" title="Supprimer">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        @empty
            <div class="empty-chain">Aucune règle Mangle configurée</div>
        @endforelse
    </div>
</div>

<!-- Modal Mangle -->
<div id="mangleModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="mangleModalTitle"><i class="fas fa-tags"></i> Ajouter règle Mangle</h3>
            <button onclick="closeMangleModal()" class="btn-close">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="mangleRuleId">
            
            <div class="form-row">
                <div class="form-group">
                    <label>Chaîne:</label>
                    <select id="mangleChain" class="input-field">
                        <option value="prerouting">prerouting (avant routage)</option>
                        <option value="postrouting">postrouting (après routage)</option>
                        <option value="forward">forward (forward)</option>
                        <option value="input">input (entrant)</option>
                        <option value="output">output (sortant)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Action:</label>
                    <select id="mangleAction" class="input-field">
                        <option value="accept">✅ accept</option>
                        <option value="drop">❌ drop</option>
                        <option value="mark-routing">🔄 mark-routing</option>
                        <option value="mark-connection">🔗 mark-connection</option>
                        <option value="mark-packet">📦 mark-packet</option>
                        <option value="sniff-pc">👁️ sniff-pc</option>
                        <option value="sniff-tzsp">👁️ sniff-tzsp</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Protocole:</label>
                    <select id="mangleProtocol" class="input-field">
                        <option value="">— Tous —</option>
                        <option value="tcp">TCP</option>
                        <option value="udp">UDP</option>
                        <option value="icmp">ICMP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Commentaire:</label>
                    <input type="text" id="mangleComment" class="input-field" placeholder="Description...">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Adresse Source:</label>
                    <input type="text" id="mangleSrcAddress" class="input-field" placeholder="192.168.1.0/24">
                </div>
                <div class="form-group">
                    <label>Port Source:</label>
                    <input type="text" id="mangleSrcPort" class="input-field" placeholder="1024-65535">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Adresse Destination:</label>
                    <input type="text" id="mangleDstAddress" class="input-field" placeholder="0.0.0.0/0">
                </div>
                <div class="form-group">
                    <label>Port Destination:</label>
                    <input type="text" id="mangleDstPort" class="input-field" placeholder="80,443">
                </div>
            </div>
            
            <div class="form-row mark-section" style="background: rgba(102,126,234,0.1); padding: 1rem; border-radius: 0.5rem;">
                <div class="form-group">
                    <label>New Routing Mark:</label>
                    <input type="text" id="mangleNewRoutingMark" class="input-field" placeholder="Route_LAN1">
                </div>
                <div class="form-group">
                    <label>New Connection Mark:</label>
                    <input type="text" id="mangleNewConnectionMark" class="input-field" placeholder="Conn_HTTP">
                </div>
            </div>
            
            <div class="form-row mark-section" style="background: rgba(46,247,91,0.1); padding: 1rem; border-radius: 0.5rem; margin-top: 0.5rem;">
                <div class="form-group">
                    <label>New Packet Mark:</label>
                    <input type="text" id="mangleNewPacketMark" class="input-field" placeholder="Pkt_Priority">
                </div>
                <div class="form-group">
                    <label>Passthrough:</label>
                    <select id="manglePassthrough" class="input-field">
                        <option value="1">Yes (continuer les règles)</option>
                        <option value="0">No (arrêter ici)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Interface Entrante:</label>
                    <input type="text" id="mangleInInterface" class="input-field" placeholder="ether1">
                </div>
                <div class="form-group">
                    <label>Interface Sortante:</label>
                    <input type="text" id="mangleOutInterface" class="input-field" placeholder="ether2">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeMangleModal()" class="btn-secondary">Annuler</button>
            <button onclick="saveMangleRule()" class="btn-primary">Enregistrer</button>
        </div>
    </div>
</div>

<style>
.mangle-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.info-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem;
    background: rgba(0,0,0,0.2);
    border-radius: 0.5rem;
    border: 1px solid #2e4b6b;
}
.info-item i {
    color: #00a6ff;
}
.info-item span {
    font-size: 0.9rem;
    color: #8ba9d0;
}
.badge-chain-prerouting { background: rgba(102,126,234,0.2); color: #667eea; }
.badge-chain-postrouting { background: rgba(0,166,255,0.2); color: #00a6ff; }
.badge-chain-forward { background: rgba(46,247,91,0.2); color: #2ef75b; }
.badge-chain-input { background: rgba(255,170,51,0.2); color: #ffaa33; }
.badge-chain-output { background: rgba(255,94,124,0.2); color: #ff5e7c; }
.badge-action-mark-routing { background: rgba(102,126,234,0.2); color: #667eea; }
.badge-action-mark-connection { background: rgba(0,166,255,0.2); color: #00a6ff; }
.badge-action-mark-packet { background: rgba(46,247,91,0.2); color: #2ef75b; }
.mark-section {
    border: 1px solid #2e4b6b;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 2000;
    align-items: center;
    justify-content: center;
}
.modal.show {
    display: flex;
}
.modal-content {
    background: #132231;
    border-radius: 1rem;
    border: 1px solid #2e4b6b;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    min-width: 400px;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #2e4b6b;
}
.modal-header h3 {
    margin: 0;
    color: #fff;
}
.btn-close {
    background: none;
    border: none;
    color: #8ba9d0;
    font-size: 1.5rem;
    cursor: pointer;
}
.modal-body {
    padding: 1.5rem;
}
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid #2e4b6b;
}
</style>

<script>
const routeurId = {{ $routeur->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

// Modal Mangle
window.openMangleModal = function() {
    document.getElementById('mangleRuleId').value = '';
    document.getElementById('mangleModalTitle').innerHTML = '<i class="fas fa-tags"></i> Ajouter règle Mangle';
    document.getElementById('mangleChain').value = 'prerouting';
    document.getElementById('mangleAction').value = 'accept';
    document.getElementById('mangleProtocol').value = '';
    document.getElementById('mangleComment').value = '';
    document.getElementById('mangleSrcAddress').value = '';
    document.getElementById('mangleSrcPort').value = '';
    document.getElementById('mangleDstAddress').value = '';
    document.getElementById('mangleDstPort').value = '';
    document.getElementById('mangleNewRoutingMark').value = '';
    document.getElementById('mangleNewConnectionMark').value = '';
    document.getElementById('mangleNewPacketMark').value = '';
    document.getElementById('manglePassthrough').value = '1';
    document.getElementById('mangleInInterface').value = '';
    document.getElementById('mangleOutInterface').value = '';
    document.getElementById('mangleModal').classList.add('show');
};

window.editMangleRule = function(id, rule) {
    document.getElementById('mangleRuleId').value = id;
    document.getElementById('mangleModalTitle').innerHTML = '<i class="fas fa-edit"></i> Modifier règle Mangle';
    document.getElementById('mangleChain').value = rule.chain || 'prerouting';
    document.getElementById('mangleAction').value = rule.action || 'accept';
    document.getElementById('mangleProtocol').value = rule.protocol || '';
    document.getElementById('mangleComment').value = rule.comment || '';
    document.getElementById('mangleSrcAddress').value = rule.src_address || '';
    document.getElementById('mangleSrcPort').value = rule.src_port || '';
    document.getElementById('mangleDstAddress').value = rule.dst_address || '';
    document.getElementById('mangleDstPort').value = rule.dst_port || '';
    document.getElementById('mangleNewRoutingMark').value = rule.new_routing_mark || '';
    document.getElementById('mangleNewConnectionMark').value = rule.new_connection_mark || '';
    document.getElementById('mangleNewPacketMark').value = rule.new_packet_mark || '';
    document.getElementById('manglePassthrough').value = rule.passthrough ? '1' : '0';
    document.getElementById('mangleInInterface').value = rule.in_interface || '';
    document.getElementById('mangleOutInterface').value = rule.out_interface || '';
    document.getElementById('mangleModal').classList.add('show');
};

window.closeMangleModal = function() {
    document.getElementById('mangleModal').classList.remove('show');
};

window.saveMangleRule = async function() {
    const id = document.getElementById('mangleRuleId').value;
    const data = {
        chain: document.getElementById('mangleChain').value,
        action: document.getElementById('mangleAction').value,
        protocol: document.getElementById('mangleProtocol').value,
        comment: document.getElementById('mangleComment').value,
        src_address: document.getElementById('mangleSrcAddress').value,
        src_port: document.getElementById('mangleSrcPort').value,
        dst_address: document.getElementById('mangleDstAddress').value,
        dst_port: document.getElementById('mangleDstPort').value,
        new_routing_mark: document.getElementById('mangleNewRoutingMark').value,
        new_connection_mark: document.getElementById('mangleNewConnectionMark').value,
        new_packet_mark: document.getElementById('mangleNewPacketMark').value,
        passthrough: document.getElementById('manglePassthrough').value === '1',
        in_interface: document.getElementById('mangleInInterface').value,
        out_interface: document.getElementById('mangleOutInterface').value,
    };
    
    const url = id ? `/routeurs/${routeurId}/firewall/mangle/${id}` : `/routeurs/${routeurId}/firewall/mangle`;
    const method = id ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        
        if (result.success) {
            showStatus(result.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showStatus(result.message, 'error');
        }
    } catch (e) {
        showStatus('Erreur: ' + e.message, 'error');
    }
};

window.toggleMangleRule = async function(id, enable) {
    if (!confirm(`${enable ? 'Activer' : 'Désactiver'} cette règle Mangle ?`)) return;
    
    try {
        const response = await fetch(`/routeurs/${routeurId}/firewall/mangle/${id}/${enable ? 'enable' : 'disable'}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await response.json();
        
        if (data.success) {
            showStatus(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showStatus(data.message, 'error');
        }
    } catch (e) {
        showStatus('Erreur: ' + e.message, 'error');
    }
};

window.deleteMangleRule = async function(id) {
    if (!confirm('Supprimer définitivement cette règle Mangle ?')) return;
    
    try {
        const response = await fetch(`/routeurs/${routeurId}/firewall/mangle/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await response.json();
        
        if (data.success) {
            showStatus(data.message, 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showStatus(data.message, 'error');
        }
    } catch (e) {
        showStatus('Erreur: ' + e.message, 'error');
    }
};

document.getElementById('mangleModal')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeMangleModal();
});
</script>
