<?php

namespace App\Http\Controllers;

use App\Models\InterfaceModel;
use App\Models\Routeur;
use App\Models\Statistique;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatistiqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $since = Carbon::now()->subDay();

        $trafficStats = Statistique::where('type', 'traffic')
            ->where('timestamp', '>=', $since)
            ->get();

        $totalTrafficKb = $trafficStats->sum('valeur');
        $totalTrafficTb = round($totalTrafficKb / 1024 / 1024, 2); // TB

        $peakTraffic = $trafficStats->max('valeur') ?? 0;

        $totalRouteurs = Routeur::count();
        $onlineRouteurs = Routeur::where('statut', 'en_ligne')->count();
        $availability = $totalRouteurs > 0 ? round(($onlineRouteurs / $totalRouteurs) * 100, 2) : 100;

        $activeUsers = User::count();

        $hourlyTrafficRaw = Statistique::where('type', 'traffic')
            ->where('timestamp', '>=', $since)
            ->selectRaw('HOUR(timestamp) as hour, SUM(valeur) as total')
            ->groupByRaw('HOUR(timestamp)')
            ->orderBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        $hourlyTraffic = collect(range(0, 23))->mapWithKeys(function ($hour) use ($hourlyTrafficRaw) {
            return [$hour => round($hourlyTrafficRaw[$hour] ?? 0, 2)];
        })->toArray();

        $trafficDistribution = Statistique::where('timestamp', '>=', $since)
            ->selectRaw('type, SUM(valeur) as total')
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        $topInterfaceTraffic = Statistique::where('type', 'traffic')
            ->selectRaw('interface_id, SUM(valeur) as total')
            ->groupBy('interface_id')
            ->orderByDesc('total')
            ->take(4)
            ->get();

        $topEmitters = $topInterfaceTraffic->map(function ($row) {
            $interface = InterfaceModel::find($row->interface_id);
            return [
                'name' => $interface?->nom ?? 'Unknown',
                'value' => round($row->total, 2),
            ];
        });

        return view('statistique', compact(
            'totalTrafficTb',
            'activeUsers',
            'availability',
            'peakTraffic',
            'totalRouteurs',
            'hourlyTraffic',
            'trafficDistribution',
            'topEmitters'
        ));
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
    public function show(Statistique $statistique)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Statistique $statistique)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Statistique $statistique)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Statistique $statistique)
    {
        //
    }
}
