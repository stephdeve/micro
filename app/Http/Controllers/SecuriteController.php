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
        $stats = [
            'niveau_securite' => min(100, max(0, 100 - (Securite::where('severite', 'critique')->count() * 4))),
            'tentatives_bloc' => $incidentsIntrusion->count(),
            'tentatives_bloc_today' => $incidentsIntrusion->whereDate('created_at', today())->count(),
            'regles_firewall' => FirewallRule::where('est_active', true)->count(),
            'connexions_tls' => DB::table('sessions')->where('last_activity', '>=', now()->subMinutes(15)->timestamp)->count(),
            'alertes_recente' => Securite::latest()->take(5)->get(),
        ];

        return view('securite', compact('stats'));
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
