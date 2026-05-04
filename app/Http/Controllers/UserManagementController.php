<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:super_admin']);
    }

    /**
     * Afficher la liste des utilisateurs (admin réseau et admin service)
     */
    public function index()
    {
        // Récupérer les utilisateurs par rôle
        $adminReseauUsers = User::role('admin_reseau')
            ->orderBy('created_at', 'desc')
            ->get();

        $adminServiceUsers = User::role('admin_service')
            ->orderBy('created_at', 'desc')
            ->get();

        $superAdmins = User::role('super_admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.users.index', compact('adminReseauUsers', 'adminServiceUsers', 'superAdmins'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $roles = Role::whereIn('name', ['admin_reseau', 'admin_service'])->get();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Créer un nouvel utilisateur admin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
            'role' => 'required|in:admin_reseau,admin_service',
            'telephone' => 'nullable|string|max:20',
            'est_actif' => 'boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telephone' => $validated['telephone'] ?? null,
            'est_actif' => $validated['est_actif'] ?? true,
            'email_verified_at' => now(), // Auto-vérifié car créé par superadmin
        ]);

        // Attribuer le rôle
        $user->assignRole($validated['role']);

        // Envoyer un email de notification avec le mot de passe temporaire (optionnel)
        // Password::sendResetLink(['email' => $user->email]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur "' . $user->name . '" créé avec succès en tant que ' . $validated['role']);
    }

    /**
     * Afficher les détails d'un utilisateur
     */
    public function show(User $user)
    {
        // Empêcher de voir un autre superadmin
        if ($user->hasRole('super_admin') && auth()->id() !== $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas voir les détails d\'un autre superadmin');
        }

        $user->load(['routeurs', 'employes', 'roles']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        // Empêcher la modification d'un superadmin
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas modifier un superadmin');
        }

        $roles = Role::whereIn('name', ['admin_reseau', 'admin_service'])->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        // Empêcher la modification d'un superadmin
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas modifier un superadmin');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
            'role' => 'required|in:admin_reseau,admin_service',
            'telephone' => 'nullable|string|max:20',
            'est_actif' => 'boolean',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'est_actif' => $validated['est_actif'] ?? true,
        ]);

        // Mettre à jour le mot de passe si fourni
        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Mettre à jour le rôle
        $user->syncRoles([$validated['role']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur "' . $user->name . '" mis à jour');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression d'un superadmin
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer un superadmin');
        }

        // Empêcher l'auto-suppression
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas vous supprimer vous-même');
        }

        $nom = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur "' . $nom . '" supprimé');
    }

    /**
     * Activer/Désactiver un utilisateur
     */
    public function toggle(User $user)
    {
        // Empêcher la désactivation d'un superadmin
        if ($user->hasRole('super_admin')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas désactiver un superadmin');
        }

        // Empêcher l'auto-désactivation
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas vous désactiver vous-même');
        }

        $user->est_actif = !$user->est_actif;
        $user->save();

        $status = $user->est_actif ? 'activé' : 'désactivé';
        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur "' . $user->name . '" ' . $status);
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword(Request $request, User $user)
    {
        // Empêcher la modification d'un superadmin
        if ($user->hasRole('super_admin')) {
            return response()->json(['error' => 'Impossible de modifier un superadmin'], 403);
        }

        $validated = $request->validate([
            'password' => ['required', PasswordRule::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe réinitialisé pour ' . $user->name
        ]);
    }

    /**
     * Générer un mot de passe aléatoire sécurisé
     */
    public function generatePassword()
    {
        $password = bin2hex(random_bytes(8)); // 16 caractères hex
        return response()->json(['password' => $password]);
    }
}
