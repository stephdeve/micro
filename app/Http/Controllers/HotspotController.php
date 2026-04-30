<?php

namespace App\Http\Controllers;

use App\Models\HotspotProfile;
use App\Models\HotspotUser;
use App\Models\Routeur;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HotspotController extends Controller
{
    private MikrotikService $mikrotik;

    public function __construct(MikrotikService $mikrotik)
    {
        $this->middleware('auth')->except(['showLogin', 'doLogin']);
        $this->mikrotik = $mikrotik;
    }

    /**
     * Afficher le dashboard Hotspot
     */
    public function index(Routeur $routeur)
    {
        $profiles = $routeur->hotspotProfiles()->where('active', true)->orderBy('nom')->get();
        $users = $routeur->hotspotUsers()->orderBy('created_at', 'desc')->paginate(20);
        $activeUsers = $routeur->hotspotUsers()->where('disabled', false)->count();
        $onlineUsers = 0;
        
        // Récupérer les utilisateurs actifs sur le routeur
        if ($routeur->statut === 'en_ligne') {
            $onlineUsers = count($this->mikrotik->getHotspotActiveUsers($routeur));
        }

        return view('reseau.hotspot', compact('routeur', 'profiles', 'users', 'activeUsers', 'onlineUsers'));
    }

    /**
     * Créer un profil Hotspot
     */
    public function storeProfile(Request $request, Routeur $routeur)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'shared_users' => 'integer|min:1|max:50',
            'rate_limit' => 'nullable|string',
            'session_timeout' => 'nullable|string',
            'idle_timeout' => 'nullable|string',
            'commentaire' => 'nullable|string',
        ]);

        $profile = new HotspotProfile([
            'routeur_id' => $routeur->id,
            'nom' => $validated['nom'],
            'shared_users' => $validated['shared_users'] ?? 1,
            'rate_limit' => $validated['rate_limit'] ?? null,
            'session_timeout' => $validated['session_timeout'] ?? null,
            'idle_timeout' => $validated['idle_timeout'] ?? '00:05:00',
            'commentaire' => $validated['commentaire'] ?? null,
            'active' => true,
        ]);

        $profile->save();

        // Synchroniser avec MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->syncProfileToMikrotik($routeur, $profile);
        }

        return redirect()->route('hotspot', $routeur)
            ->with('success', 'Profil Hotspot créé avec succès');
    }

    /**
     * Créer un utilisateur Hotspot
     */
    public function storeUser(Request $request, Routeur $routeur)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:hotspot_users',
            'password' => 'nullable|string|min:4|max:50',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'type' => 'required|in:voucher,employe,invite,permanent',
            'nom_complet' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'mac_address' => 'nullable|string|max:17',
            'data_limit' => 'nullable|integer|min:0',
            'time_limit' => 'nullable|string',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'commentaire' => 'nullable|string',
        ]);

        // Générer un mot de passe si vide
        $password = $validated['password'] ?? Str::random(8);

        $user = new HotspotUser([
            'routeur_id' => $routeur->id,
            'profile_id' => $validated['profile_id'] ?? null,
            'username' => $validated['username'],
            'password' => $password,
            'type' => $validated['type'],
            'nom_complet' => $validated['nom_complet'] ?? null,
            'email' => $validated['email'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'mac_address' => $validated['mac_address'] ?? null,
            'data_limit' => $validated['data_limit'] ?? null,
            'time_limit' => $validated['time_limit'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'commentaire' => $validated['commentaire'] ?? null,
            'disabled' => false,
        ]);

        $user->save();

        // Synchroniser avec MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->syncUserToMikrotik($routeur, $user);
        }

        return redirect()->route('hotspot', $routeur)
            ->with('success', 'Utilisateur Hotspot "' . $user->username . '" créé avec succès. Mot de passe: ' . $password);
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function updateUser(Request $request, Routeur $routeur, HotspotUser $user)
    {
        $validated = $request->validate([
            'password' => 'nullable|string|min:4|max:50',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'disabled' => 'boolean',
            'commentaire' => 'nullable|string',
        ]);

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }
        $user->profile_id = $validated['profile_id'] ?? $user->profile_id;
        $user->disabled = $validated['disabled'] ?? $user->disabled;
        $user->commentaire = $validated['commentaire'] ?? $user->commentaire;
        $user->save();

        // Resynchroniser avec MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->syncUserToMikrotik($routeur, $user);
        }

        return redirect()->route('hotspot', $routeur)
            ->with('success', 'Utilisateur "' . $user->username . '" mis à jour');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroyUser(Routeur $routeur, HotspotUser $user)
    {
        $username = $user->username;

        // Supprimer de MikroTik
        if ($routeur->statut === 'en_ligne') {
            $this->mikrotik->removeHotspotUser($routeur, $user->mikrotik_id ?? $username);
        }

        $user->delete();

        return redirect()->route('hotspot', $routeur)
            ->with('success', 'Utilisateur "' . $username . '" supprimé');
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggleUser(Routeur $routeur, HotspotUser $user)
    {
        $user->disabled = !$user->disabled;
        $user->save();

        if ($routeur->statut === 'en_ligne') {
            $this->syncUserToMikrotik($routeur, $user);
        }

        $status = $user->disabled ? 'désactivé' : 'activé';
        return redirect()->route('hotspot', $routeur)
            ->with('success', 'Utilisateur "' . $user->username . '" ' . $status);
    }

    /**
     * Générer des vouchers en masse
     */
    public function generateVouchers(Request $request, Routeur $routeur)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
            'profile_id' => 'nullable|exists:hotspot_profiles,id',
            'data_limit' => 'nullable|integer|min:0',
            'time_limit' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'prefix' => 'nullable|string|max:10',
        ]);

        $quantity = $validated['quantity'];
        $prefix = $validated['prefix'] ?? 'WIFI';
        $vouchers = [];

        for ($i = 0; $i < $quantity; $i++) {
            $code = $prefix . '-' . Str::upper(Str::random(6));
            $password = Str::random(8);

            $user = new HotspotUser([
                'routeur_id' => $routeur->id,
                'profile_id' => $validated['profile_id'] ?? null,
                'username' => $code,
                'password' => $password,
                'type' => 'voucher',
                'data_limit' => $validated['data_limit'] ?? null,
                'time_limit' => $validated['time_limit'] ?? null,
                'valid_until' => $validated['valid_until'] ?? null,
                'disabled' => false,
            ]);
            $user->save();
            $vouchers[] = $user;

            // Synchroniser avec MikroTik
            if ($routeur->statut === 'en_ligne') {
                $this->syncUserToMikrotik($routeur, $user);
            }
        }

        return redirect()->route('hotspot', $routeur)
            ->with('success', $quantity . ' vouchers générés avec succès');
    }

    /**
     * Récupérer les utilisateurs actifs (AJAX)
     */
    public function getActiveUsers(Routeur $routeur)
    {
        if ($routeur->statut !== 'en_ligne') {
            return response()->json(['users' => []]);
        }

        $users = $this->mikrotik->getHotspotActiveUsers($routeur);
        return response()->json(['users' => $users, 'count' => count($users)]);
    }

    /**
     * Déconnecter un utilisateur actif
     */
    public function disconnectUser(Routeur $routeur, Request $request)
    {
        $username = $request->input('username');
        
        if ($routeur->statut === 'en_ligne') {
            $this->mikrotik->disconnectHotspotUser($routeur, $username);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Synchroniser un profil avec MikroTik
     */
    private function syncProfileToMikrotik(Routeur $routeur, HotspotProfile $profile): bool
    {
        $mikrotikName = $this->mikrotik->setHotspotUserProfile(
            $routeur,
            $profile->nom,
            $profile->shared_users,
            $profile->rate_limit,
            $profile->session_timeout,
            $profile->idle_timeout
        );

        if ($mikrotikName) {
            $profile->mikrotik_name = $mikrotikName;
            $profile->save();
        }

        return !empty($mikrotikName);
    }

    /**
     * Synchroniser un utilisateur avec MikroTik
     */
    private function syncUserToMikrotik(Routeur $routeur, HotspotUser $user): bool
    {
        $profileName = $user->profile?->mikrotik_name ?? 'default';
        
        $mikrotikId = $this->mikrotik->setHotspotUser(
            $routeur,
            $user->username,
            $user->password,
            $profileName,
            $user->mac_address,
            $user->disabled,
            $user->data_limit,
            $user->time_limit
        );

        if ($mikrotikId) {
            $user->mikrotik_id = $mikrotikId;
            $user->save();
        }

        return !empty($mikrotikId);
    }

    /**
     * Afficher la page de login du portail captif (publique)
     */
    public function showLogin(Routeur $routeur)
    {
        return view('hotspot.login', compact('routeur'));
    }

    /**
     * Traiter la connexion au portail captif
     */
    public function doLogin(Request $request, Routeur $routeur)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50',
            'password' => 'required|string|max:50',
        ]);

        // Vérifier si l'utilisateur existe et est actif
        $user = HotspotUser::where('routeur_id', $routeur->id)
            ->where('username', $validated['username'])
            ->where('disabled', false)
            ->first();

        if (!$user || $user->password !== $validated['password']) {
            return back()->with('error', 'Identifiants incorrects');
        }

        // Vérifier la validité temporelle
        $now = now();
        if ($user->valid_from && $now->lt($user->valid_from)) {
            return back()->with('error', 'Ce compte n\'est pas encore actif');
        }
        if ($user->valid_until && $now->gt($user->valid_until)) {
            return back()->with('error', 'Ce compte a expiré');
        }

        // Si le routeur est en ligne, authentifier via MikroTik
        if ($routeur->statut === 'en_ligne') {
            $activeUsers = $this->mikrotik->getHotspotActiveUsers($routeur);
            
            // Vérifier si déjà connecté
            $alreadyConnected = collect($activeUsers)->firstWhere('user', $user->username);
            
            if (!$alreadyConnected) {
                // Essayer de connecter via l'API MikroTik
                // Note: La connexion réelle se fait via le processus captive portal de MikroTik
                // Cette vérification sert à valider les credentials
            }
        }

        // Enregistrer la session de connexion
        $user->last_login = $now;
        $user->save();

        // Si c'est une requête depuis le captive portal MikroTik
        if ($request->has('dst')) {
            // Rediriger vers la destination originale ou une page de succès
            return redirect($request->input('dst', '/'))
                ->with('success', 'Connexion réussie ! Vous avez maintenant accès à Internet.');
        }

        // Retourner à la page de login avec succès
        return back()->with('success', 'Connexion réussie !');
    }
}
