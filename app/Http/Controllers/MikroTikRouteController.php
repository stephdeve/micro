<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use Illuminate\Http\Request;
use App\Services\MikrotikService;

class MikroTikRouteController extends Controller
{
    private MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->middleware('auth');
        $this->mikrotik = $mikrotik;
    }

    /**
     * Afficher la table de routage
     */
    public function index(Routeur $routeur)
    {
        $routes = [];
        
        if ($routeur->statut === 'en_ligne') {
            $routes = $this->mikrotik->getRoutes($routeur);
        }

        return view('reseau.routes', compact('routeur', 'routes'));
    }

    /**
     * Rafraîchir la table de routage
     */
    public function sync(Routeur $routeur)
    {
        try {
            $routes = $this->mikrotik->getRoutes($routeur);
            
            return response()->json([
                'success' => true,
                'message' => count($routes) . ' routes récupérées',
                'routes' => $routes
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter une route statique
     */
    public function store(Request $request, Routeur $routeur)
    {
        $request->validate([
            'dst_address' => 'required|string|regex:/^\d+\.\d+\.\d+\.\d+\/\d+$/',
            'gateway' => 'required|ip',
            'distance' => 'nullable|integer|min:1|max:255',
            'comment' => 'nullable|string|max:255',
            'check_gateway' => 'nullable|in:ping,arp,none',
        ]);

        $config = [
            'dst_address' => $request->input('dst_address'),
            'gateway' => $request->input('gateway'),
            'distance' => $request->input('distance', 1),
            'comment' => $request->input('comment'),
            'check_gateway' => $request->input('check_gateway', ''),
        ];

        try {
            $success = $this->mikrotik->addRoute($routeur, $config);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Route ajoutée avec succès' : 'Échec de l\'ajout de la route - vérifiez la connexion au routeur'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier une route
     */
    public function update(Request $request, Routeur $routeur, string $routeId)
    {
        $request->validate([
            'dst_address' => 'nullable|string|regex:/^\d+\.\d+\.\d+\.\d+\/\d+$/',
            'gateway' => 'nullable|ip',
            'distance' => 'nullable|integer|min:1|max:255',
            'comment' => 'nullable|string|max:255',
            'check_gateway' => 'nullable|in:ping,arp,none',
        ]);

        $config = array_filter([
            'dst_address' => $request->input('dst_address'),
            'gateway' => $request->input('gateway'),
            'distance' => $request->input('distance'),
            'comment' => $request->input('comment'),
            'check_gateway' => $request->input('check_gateway'),
        ], fn($v) => $v !== null);

        $success = $this->mikrotik->updateRoute($routeur, $routeId, $config);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Route modifiée avec succès' : 'Échec de la modification'
        ]);
    }

    /**
     * Supprimer une route
     */
    public function destroy(Routeur $routeur, string $routeId)
    {
        $success = $this->mikrotik->removeRoute($routeur, $routeId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Route supprimée' : 'Échec de la suppression'
        ]);
    }

    /**
     * Activer une route
     */
    public function enable(Routeur $routeur, string $routeId)
    {
        $success = $this->mikrotik->enableRoute($routeur, $routeId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Route activée' : 'Échec de l\'activation'
        ]);
    }

    /**
     * Désactiver une route
     */
    public function disable(Routeur $routeur, string $routeId)
    {
        $success = $this->mikrotik->disableRoute($routeur, $routeId);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Route désactivée' : 'Échec de la désactivation'
        ]);
    }
}
