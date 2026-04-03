<?php

namespace App\Http\Controllers;

use App\Models\Securite;
use App\Models\FirewallRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecuriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $incidentsIntrusion = Securite::where('type', 'intrusion');

        $activeFirewallRules = FirewallRule::where('est_active', true)->orderBy('numero_ordre')->get();

        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->where('sessions.last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->orderBy('sessions.last_activity', 'desc')
            ->select('sessions.*', 'users.name as user_name', 'users.email as user_email')
            ->get();

        $alertes = Securite::where('statut', '!=', 'ignore')->latest()->paginate(3);

        $stats = [
            'niveau_securite' => min(100, max(0, 100 - (Securite::where('severite', 'critique')->count() * 4))),
            'tentatives_bloc' => $incidentsIntrusion->count(),
            'tentatives_bloc_today' => $incidentsIntrusion->whereDate('created_at', today())->count(),
            'regles_firewall' => $activeFirewallRules->count(),
            'regles_firewall_list' => $activeFirewallRules,
            'connexions_tls' => $activeSessions->count(),
            'sessions_actives' => $activeSessions,
            'alertes_non_resolues' => Securite::where('statut', '!=', 'resolu')->count(),
        ];

        return view('securite', compact('stats', 'alertes'));
    }

    public function data()
    {
        $incidentsIntrusion = Securite::where('type', 'intrusion');
        $activeFirewallRules = FirewallRule::where('est_active', true)->orderBy('numero_ordre')->get();
        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->where('sessions.last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->orderBy('sessions.last_activity', 'desc')
            ->select('sessions.*', 'users.name as user_name', 'users.email as user_email')
            ->get();

        $alertes = Securite::where('statut', '!=', 'ignore')->latest()->paginate(3);

        $stats = [
            'niveau_securite' => min(100, max(0, 100 - (Securite::where('severite', 'critique')->count() * 4))),
            'tentatives_bloc' => $incidentsIntrusion->count(),
            'tentatives_bloc_today' => $incidentsIntrusion->whereDate('created_at', today())->count(),
            'regles_firewall' => $activeFirewallRules->count(),
            'regles_firewall_list' => $activeFirewallRules,
            'connexions_tls' => $activeSessions->count(),
            'sessions_actives' => $activeSessions,
            'alertes' => $alertes->items(),
            'alertes_total' => $alertes->total(),
            'alertes_last_page' => $alertes->lastPage(),
            'alertes_current_page' => $alertes->currentPage(),
            'alertes_non_resolues' => Securite::where('statut', '!=', 'resolu')->count(),
        ];

        return response()->json($stats);
    }

    public function addFirewallRule(Request $request)
    {
        $routeurId = $request->input('routeur_id', null);

        if (! $routeurId) {
            $routeur = \App\Models\Routeur::first();
            if (! $routeur) {
                return response()->json(['message' => 'Aucun routeur configuré, impossible d’ajouter une règle.'], 422);
            }
            $routeurId = $routeur->id;
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'chain' => 'required|in:input,output,forward,prerouting,postrouting',
            'action' => 'required|in:accept,drop,reject,jump,log',
            'description' => 'nullable|string',
        ]);

        $rule = FirewallRule::create([
            'routeur_id' => $routeurId,
            'numero_ordre' => FirewallRule::max('numero_ordre') + 1,
            'nom' => $validated['nom'],
            'action' => $validated['action'],
            'chain' => $validated['chain'],
            'description' => $validated['description'] ?? 'Règle ajoutée depuis sécurité',
            'est_active' => true,
            'configuration_complete' => json_encode(['source' => 'securite', 'nom' => $validated['nom']]),
        ]);

        return response()->json(['message' => 'Règle firewall ajoutée.', 'rule' => $rule]);
    }

    public function resolveAlerte(Securite $securite)
    {
        $securite->update([
            'statut' => 'resolu',
            'resolu_a' => now(),
            'resolu_par' => auth()->id(),
        ]);

        return response()->json(['message' => 'Alerte marquée comme résolue.']);
    }

    public function archiveAlerte(Securite $securite)
    {
        $securite->update(['statut' => 'ignore']);

        return response()->json(['message' => 'Alerte archivée.']);
    }

    public function deleteAlerte(Securite $securite)
    {
        $securite->delete();
        return response()->json(['message' => 'Alerte supprimée.']);
    }

    public function destroySession($id)
    {
        if (! DB::table('sessions')->where('id', $id)->exists()) {
            return response()->json(['message' => 'Session introuvable.'], 404);
        }

        DB::table('sessions')->where('id', $id)->delete();

        return response()->json(['message' => 'Session déconnectée avec succès.']);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Securite $securite)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Securite $securite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Securite $securite)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Securite $securite)
    {
        //
    }
}
