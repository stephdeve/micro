<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use Illuminate\Http\Request;
use App\Services\MikrotikService;

class MikroTikFirewallController extends Controller
{
    private MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->middleware('auth');
        $this->mikrotik = $mikrotik;
    }

    /**
     * Page principale du pare-feu avec onglets
     */
    public function index(Routeur $routeur, Request $request)
    {
        $tab = $request->input('tab', 'filter');
        
        $filterRules = [];
        $natRules = [];
        $mangleRules = [];

        if ($routeur->statut === 'en_ligne') {
            switch ($tab) {
                case 'filter':
                    $filterRules = $this->mikrotik->getFirewallRules($routeur);
                    break;
                case 'nat':
                    $natRules = $this->mikrotik->getNatRules($routeur);
                    break;
                case 'mangle':
                    $mangleRules = $this->mikrotik->getMangleRules($routeur);
                    break;
            }
        }

        // Grouper les règles Filter par chaîne
        $groupedFilters = [
            'INPUT' => [],
            'OUTPUT' => [],
            'FORWARD' => []
        ];
        foreach ($filterRules as $rule) {
            $chain = $rule['chain'] ?? 'FORWARD';
            if (!isset($groupedFilters[$chain])) {
                $groupedFilters[$chain] = [];
            }
            $groupedFilters[$chain][] = $rule;
        }

        return view('reseau.firewall', compact('routeur', 'tab', 'groupedFilters', 'natRules', 'mangleRules'));
    }

    // ==================== FILTER RULES ====================

    public function storeFilter(Request $request, Routeur $routeur)
    {
        $request->validate([
            'chain' => 'required|in:INPUT,OUTPUT,FORWARD',
            'action' => 'required|in:accept,drop,reject,log,jump,return',
            'protocol' => 'nullable|in:tcp,udp,icmp,all',
            'src_address' => 'nullable|string',
            'dst_address' => 'nullable|string',
            'src_port' => 'nullable|string',
            'dst_port' => 'nullable|string',
            'in_interface' => 'nullable|string',
            'out_interface' => 'nullable|string',
            'comment' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['chain', 'action', 'protocol', 'src_address', 'dst_address', 
                                'src_port', 'dst_port', 'in_interface', 'out_interface', 'comment']);

        $success = $this->mikrotik->addFirewallRule($routeur, $data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle ajoutée' : 'Échec de l\'ajout'
        ]);
    }

    public function updateFilter(Request $request, Routeur $routeur, string $ruleId)
    {
        $data = $request->only(['chain', 'action', 'protocol', 'src_address', 'dst_address', 
                                'src_port', 'dst_port', 'in_interface', 'out_interface', 'comment']);

        $success = $this->mikrotik->updateFirewallRule($routeur, $ruleId, $data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle modifiée' : 'Échec de la modification'
        ]);
    }

    public function destroyFilter(Routeur $routeur, string $ruleId)
    {
        $success = $this->mikrotik->removeFirewallRule($routeur, $ruleId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle supprimée' : 'Échec de la suppression'
        ]);
    }

    public function toggleFilter(Routeur $routeur, string $ruleId, bool $enable)
    {
        $success = $this->mikrotik->toggleFirewallRule($routeur, $ruleId, $enable);

        return response()->json([
            'success' => $success,
            'message' => $success ? ($enable ? 'Règle activée' : 'Règle désactivée') : 'Échec'
        ]);
    }

    public function moveFilter(Request $request, Routeur $routeur, string $ruleId)
    {
        $request->validate(['destination' => 'required|string']);
        
        $success = $this->mikrotik->moveFirewallRule($routeur, $ruleId, $request->input('destination'));

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle déplacée' : 'Échec du déplacement'
        ]);
    }

    // ==================== NAT RULES ====================

    public function storeNat(Request $request, Routeur $routeur)
    {
        $request->validate([
            'chain' => 'required|in:srcnat,dstnat',
            'action' => 'required|in:masquerade,src-nat,dst-nat,redirect,netmap,accept,drop',
            'protocol' => 'nullable|in:tcp,udp,icmp',
            'src_address' => 'nullable|string',
            'dst_address' => 'nullable|string',
            'src_port' => 'nullable|string',
            'dst_port' => 'nullable|string',
            'to_addresses' => 'nullable|string',
            'to_ports' => 'nullable|string',
            'out_interface' => 'nullable|string',
            'in_interface' => 'nullable|string',
            'comment' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['chain', 'action', 'protocol', 'src_address', 'dst_address', 
                                'src_port', 'dst_port', 'to_addresses', 'to_ports', 
                                'out_interface', 'in_interface', 'comment']);

        $success = $this->mikrotik->addNatRule($routeur, $data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle NAT ajoutée' : 'Échec de l\'ajout'
        ]);
    }

    public function updateNat(Request $request, Routeur $routeur, string $ruleId)
    {
        $data = $request->only(['chain', 'action', 'protocol', 'src_address', 'dst_address', 
                                'src_port', 'dst_port', 'to_addresses', 'to_ports', 
                                'out_interface', 'in_interface', 'comment']);

        $success = $this->mikrotik->updateNatRule($routeur, $ruleId, $data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle NAT modifiée' : 'Échec de la modification'
        ]);
    }

    public function destroyNat(Routeur $routeur, string $ruleId)
    {
        $success = $this->mikrotik->removeNatRule($routeur, $ruleId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle NAT supprimée' : 'Échec de la suppression'
        ]);
    }

    public function toggleNat(Routeur $routeur, string $ruleId, bool $enable)
    {
        $success = $this->mikrotik->toggleNatRule($routeur, $ruleId, $enable);

        return response()->json([
            'success' => $success,
            'message' => $success ? ($enable ? 'Règle NAT activée' : 'Règle NAT désactivée') : 'Échec'
        ]);
    }

    public function moveNat(Request $request, Routeur $routeur, string $ruleId)
    {
        $request->validate(['destination' => 'required|string']);
        
        $success = $this->mikrotik->moveNatRule($routeur, $ruleId, $request->input('destination'));

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle NAT déplacée' : 'Échec du déplacement'
        ]);
    }

    // ==================== MANGLE RULES ====================

    public function storeMangle(Request $request, Routeur $routeur)
    {
        $request->validate([
            'chain' => 'required|in:prerouting,postrouting,forward,input,output',
            'action' => 'required|in:accept,drop,reject,mark-routing,mark-connection,mark-packet,sniff-pc,sniff-tzsp,jump',
            'protocol' => 'nullable|in:tcp,udp,icmp',
            'src_address' => 'nullable|string',
            'dst_address' => 'nullable|string',
            'src_port' => 'nullable|string',
            'dst_port' => 'nullable|string',
            'new_routing_mark' => 'nullable|string',
            'new_connection_mark' => 'nullable|string',
            'new_packet_mark' => 'nullable|string',
            'passthrough' => 'nullable|boolean',
            'in_interface' => 'nullable|string',
            'out_interface' => 'nullable|string',
            'comment' => 'nullable|string|max:255',
        ]);

        $data = $request->only(['chain', 'action', 'protocol', 'src_address', 'dst_address', 
                                'src_port', 'dst_port', 'new_routing_mark', 'new_connection_mark', 
                                'new_packet_mark', 'passthrough', 'in_interface', 'out_interface', 'comment']);

        $success = $this->mikrotik->addMangleRule($routeur, $data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle Mangle ajoutée' : 'Échec de l\'ajout'
        ]);
    }

    public function updateMangle(Request $request, Routeur $routeur, string $ruleId)
    {
        $data = $request->only(['chain', 'action', 'protocol', 'src_address', 'dst_address', 
                                'src_port', 'dst_port', 'new_routing_mark', 'new_connection_mark', 
                                'new_packet_mark', 'passthrough', 'in_interface', 'out_interface', 'comment']);

        $success = $this->mikrotik->updateMangleRule($routeur, $ruleId, $data);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle Mangle modifiée' : 'Échec de la modification'
        ]);
    }

    public function destroyMangle(Routeur $routeur, string $ruleId)
    {
        $success = $this->mikrotik->removeMangleRule($routeur, $ruleId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle Mangle supprimée' : 'Échec de la suppression'
        ]);
    }

    public function toggleMangle(Routeur $routeur, string $ruleId, bool $enable)
    {
        $success = $this->mikrotik->toggleMangleRule($routeur, $ruleId, $enable);

        return response()->json([
            'success' => $success,
            'message' => $success ? ($enable ? 'Règle Mangle activée' : 'Règle Mangle désactivée') : 'Échec'
        ]);
    }

    public function moveMangle(Request $request, Routeur $routeur, string $ruleId)
    {
        $request->validate(['destination' => 'required|string']);
        
        $success = $this->mikrotik->moveMangleRule($routeur, $ruleId, $request->input('destination'));

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Règle Mangle déplacée' : 'Échec du déplacement'
        ]);
    }
}
