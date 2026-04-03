<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Routeur;
use App\Models\InterfaceModel;
use App\Models\Message;
use App\Models\Securite;
use App\Models\FirewallRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Récupérer les données pour le dashboard
        $routeursActifs = Routeur::where('statut', 'en_ligne')->count();
        $clientsWiFi = InterfaceModel::where('type', 'wireless')->sum('clients_connectes');
        $messagesNonLus = Message::where('receiver_id', Auth::id())
                                 ->where('is_read', false)
                                 ->count();

        $routeurPrincipal = Routeur::where('statut', 'en_ligne')->first();

        $interfacesPrincipales = $routeurPrincipal ? $routeurPrincipal->interfaces()
            ->orderByRaw("CASE WHEN statut = 'actif' THEN 0 ELSE 1 END")
            ->orderBy('debit_entrant', 'desc')
            ->orderBy('debit_sortant', 'desc')
            ->take(3)
            ->get() : collect();
        
        $topConsommateurs = InterfaceModel::select('nom', 'adresse_ip', 'debit_entrant', 'debit_sortant', 'type')
                                          ->orderByRaw('(debit_entrant + debit_sortant) DESC')
                                          ->take(3)
                                          ->get()
                                          ->map(function($interface) {
                                              $interface->debit = $interface->debit_entrant + $interface->debit_sortant;
                                              $interface->icone = match($interface->type) {
                                                  'wifi' => 'wifi',
                                                  'ethernet' => 'network-wired',
                                                  default => 'laptop'
                                              };
                                              return $interface;
                                          });
        
        $enregistrements = Routeur::with('interfaces')->take(4)->get();
        
        $derniersMessages = Message::where('receiver_id', Auth::id())
                                   ->orWhere('sender_id', Auth::id())
                                   ->with('sender')
                                   ->latest()
                                   ->take(3)
                                   ->get();
        
        // Utiliser DB au lieu d'un modèle Session
        $connexionsTLS = DB::table('sessions')
                          ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                          ->count();
        
        $reglesFirewall = FirewallRule::where('est_active', true)->count();
        
        $tentativesIntrusion = Securite::where('type', 'intrusion')
                                       ->whereDate('created_at', today())
                                       ->count();

        $alertesRecente = Securite::where('type', 'alerte')
                                   ->latest()
                                   ->take(5)
                                   ->get();
        
        $sessionsActives = DB::table('sessions')
                            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
                            ->orderBy('last_activity', 'desc')
                            ->take(3)
                            ->get();

        // Données supplémentaires pour le dashboard
        $incidentCritiques = Securite::where('severite', 'critique')
                                     ->whereDate('created_at', '>=', now()->subDay())
                                     ->count();

        $etatReseau = 'Sécurisé';
        if ($incidentCritiques > 0 || $tentativesIntrusion > 5 || $reglesFirewall < 1) {
            $etatReseau = 'Attention';
        }

        $graphData = [65, 90, 45, 80, 95, 70];
        $bandePassanteTotale = InterfaceModel::sum('debit_entrant') + InterfaceModel::sum('debit_sortant');
        $pourcentageUtilisation = $bandePassanteTotale > 0 ? min(100, round(($bandePassanteTotale / 1000) * 100)) : 0; // Supposant 1000 Mbps max

        return view('dashboard', compact(
            'routeursActifs',
            'clientsWiFi',
            'messagesNonLus',
            'routeurPrincipal',
            'interfacesPrincipales',
            'topConsommateurs',
            'enregistrements',
            'derniersMessages',
            'connexionsTLS',
            'reglesFirewall',
            'tentativesIntrusion',
            'alertesRecente',
            'sessionsActives',
            'etatReseau',
            'graphData',
            'bandePassanteTotale',
            'pourcentageUtilisation'
        ));
    }
}