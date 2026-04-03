<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use Illuminate\Http\Request;

class ParametreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Chargement des paramètres utilisateur en tableau clé => valeur pour usage simple dans Blade
        $parametres = Parametre::where('user_id', auth()->id())->pluck('valeur', 'cle')->toArray();

        return view('parametre', compact('parametres'));
    }

    /**
     * Update all parameters.
     */
    public function updateAll(Request $request)
    {
        $data = $request->all();
        
        // Handle special cases
        if (isset($data['regenerate_api_key']) && $data['regenerate_api_key']) {
            $apiKey = bin2hex(random_bytes(32));
            Parametre::updateOrCreate(
                ['user_id' => auth()->id(), 'cle' => 'api_key'],
                ['valeur' => $apiKey]
            );
            return response()->json(['success' => true, 'api_key' => $apiKey]);
        }
        
        // Update all provided parameters
        foreach ($data as $cle => $valeur) {
            if ($valeur !== null && $cle !== 'regenerate_api_key') {
                Parametre::updateOrCreate(
                    ['user_id' => auth()->id(), 'cle' => $cle],
                    ['valeur' => $valeur]
                );
            }
        }

        // Notification pour mise à jour des paramètres
        auth()->user()->notify(new \App\Notifications\GenericNotification(
            'Paramètres mis à jour',
            'Vos paramètres système ont été modifiés.',
            route('parametre.index')
        ));

        return response()->json(['success' => true]);
    }
}
