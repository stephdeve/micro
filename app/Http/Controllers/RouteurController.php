<?php

namespace App\Http\Controllers;

use App\Models\Routeur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MikrotikService;

class RouteurController extends Controller
{
    /**
     * Formulaire de création redirigé vers index (modal + query param auto-open).
     */
    public function create()
    {
        return redirect()->route('routeurs.index', ['create' => 1]);
    }

    /**
     * Afficher la liste des routeurs.
     */
    public function index(Request $request)
    {
        $query = Routeur::query();

        // Filtres
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('adresse_ip', 'like', '%' . $request->search . '%')
                  ->orWhere('modele', 'like', '%' . $request->search . '%')
                  ->orWhere('numero_serie', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('modele')) {
            $query->where('modele', $request->modele);
        }

        $routeurs = $query->with('responsable')->latest()->paginate(10);
        
        // Statistiques
        $stats = [
            'en_ligne' => Routeur::where('statut', 'en_ligne')->count(),
            'hors_ligne' => Routeur::where('statut', 'hors_ligne')->count(),
            'maintenance' => Routeur::where('statut', 'maintenance')->count(),
            'modeles' => Routeur::distinct('modele')->count('modele'),
            'derniere_version' => '7.12'
        ];

        // Liste des modèles pour le filtre
        $modeles = Routeur::distinct('modele')->whereNotNull('modele')->pluck('modele');

        return view('routeurs.index', compact('routeurs', 'stats', 'modeles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse_ip' => 'required|ip|unique:routeurs',
            'api_user' => 'nullable|string|max:100',
            'api_password' => 'nullable|string|max:100',
            'emplacement' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['statut'] = 'hors_ligne';
        $data['user_id'] = Auth::id();

        $routeur = Routeur::create($data);

        $handshakeOk = false;
        if (!empty($routeur->api_user) && !empty($routeur->api_password)) {
            $service = app(MikrotikService::class);
            $handshakeOk = $service->handshake($routeur);
        }

        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Routeur ajouté',
            "Routeur {$routeur->nom} ajouté, synchronisation " . ($handshakeOk ? 'réussie' : 'échouée'),
            route('routeurs.index')
        ));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'routeur' => $routeur, 'handshake' => $handshakeOk]);
        }

        return redirect()->route('routeurs.index')->with('success', 'Routeur ajouté avec succès; handshake '.($handshakeOk ? 'reussi' : 'echoue').'.');
    }
    /**
     * Display the specified resource.
     */
    public function show(Routeur $routeur)
    {
        $routeur->load('interfaces', 'responsable');
        return view('routeurs.show', compact('routeur'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Routeur $routeur)
    {
        if (request()->ajax()) {
            return response()->json($routeur);
        }

        // Si l'appel est direct (URL dans navigateur), on montre le formulaire d'édition.
        return view('routeurs.edit', compact('routeur'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Routeur $routeur)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'modele' => 'nullable|string|max:255',
            'adresse_ip' => 'required|ip|unique:routeurs,adresse_ip,' . $routeur->id,
            'adresse_mac' => 'nullable|string|max:17|unique:routeurs,adresse_mac,' . $routeur->id,
            'version_ros' => 'nullable|string|max:50',
            'firmware' => 'nullable|string|max:50',
            'numero_serie' => 'nullable|string|max:100|unique:routeurs,numero_serie,' . $routeur->id,
            'statut' => 'required|in:en_ligne,hors_ligne,maintenance',
            'emplacement' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $oldStatut = $routeur->statut;
        $routeur->update($request->all());

        // Notification si le statut a changé
        if ($oldStatut !== $routeur->statut) {
            $message = match($routeur->statut) {
                'en_ligne' => "Le routeur {$routeur->nom} est maintenant en ligne.",
                'hors_ligne' => "Le routeur {$routeur->nom} est maintenant hors ligne.",
                'maintenance' => "Le routeur {$routeur->nom} est en maintenance.",
            };
            Auth::user()->notify(new \App\Notifications\GenericNotification(
                'Changement de statut routeur',
                $message,
                route('routeurs.index')
            ));
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'routeur' => $routeur]);
        }

        return redirect()->route('routeurs.index')->with('success', 'Routeur mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Routeur $routeur)
    {
        $nom = $routeur->nom;
        $routeur->delete();

        // Notification pour suppression
        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Routeur supprimé',
            "Le routeur {$nom} a été supprimé du système.",
            route('routeurs.index'),
            'routeur',
            $routeur->id,
            $nom
        ));

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('routeurs.index')->with('success', 'Routeur supprimé avec succès.');
    }

    /**
     * Restaurer un routeur supprimé.
     */
    public function restore($id)
    {
        $routeur = Routeur::withTrashed()->findOrFail($id);
        $routeur->restore();

        return redirect()->route('routeurs.index')->with('success', 'Routeur restauré avec succès.');
    }

    /**
     * Synchroniser un routeur avec MikroTik.
     */
    public function sync(Routeur $routeur)
    {
        $service = app(MikrotikService::class);
        $ok = $service->handshake($routeur);

        if ($ok) {
            Auth::user()->notify(new \App\Notifications\GenericNotification(
                'Routeur synchronisé',
                "La synchronisation du routeur {$routeur->nom} est terminée.",
                route('routeurs.show', $routeur),
                'routeur',
                $routeur->id,
                $routeur->nom
            ));

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Synchronisation réussie'], 200);
            }

            return redirect()->route('routeurs.index')->with('success', 'Synchronisation réussie.');
        }

        Auth::user()->notify(new \App\Notifications\GenericNotification(
            'Échec de synchronisation',
            "La synchronisation du routeur {$routeur->nom} a échoué.",
            route('routeurs.show', $routeur),
            'routeur',
            $routeur->id,
            $routeur->nom
        ));

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => false, 'message' => 'Routeur hors ligne'], 500);
        }

        return redirect()->route('routeurs.index')->with('error', 'Routeur hors ligne.');
    }
}