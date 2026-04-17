<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function ensureAdmin()
    {
        $user = Auth::user();
        if (!$user || (!$user->hasAnyRole(['super_admin', 'admin_reseau', 'admin_service']))) {
            abort(403, 'Accès non autorisé.');
        }
    }

    public function index()
    {
        $this->ensureAdmin();
        $user = Auth::user();

        // Admin service ne voit que les employés de son service
        if ($user->isAdminService() && !$user->isSuperAdmin()) {
            $users = User::where('service_id', $user->service_id)
                ->orderBy('name')->paginate(5);
        } else {
            $users = User::orderBy('name')->paginate(5);
        }

        $roles = Role::pluck('name', 'name');
        $services = Service::pluck('nom', 'id');

        return view('users.index', compact('users', 'roles', 'services'));
    }

    public function create()
    {
        $this->ensureAdmin();
        $user = Auth::user();

        $roles = $user->isSuperAdmin()
            ? Role::pluck('name', 'name')
            : Role::whereIn('name', ['employe', 'admin_service'])->pluck('name', 'name');

        $services = Service::pluck('nom', 'id');

        return view('users.create', compact('roles', 'services'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();
        $currentUser = Auth::user();

        $allowedRoles = $currentUser->isSuperAdmin()
            ? ['super_admin', 'admin_reseau', 'admin_service', 'employe']
            : ['employe', 'admin_service'];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'fonction' => ['nullable', 'string', 'max:100'],
            'est_actif' => ['nullable', 'boolean'],
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'service_id' => ['nullable', 'exists:services,id'],
        ]);

        // Admin service ne peut créer que dans son service
        $serviceId = $currentUser->isAdminService() && !$currentUser->isSuperAdmin()
            ? $currentUser->service_id
            : $request->service_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'fonction' => $request->fonction,
            'est_actif' => $request->filled('est_actif'),
            'service_id' => $serviceId,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();
        $currentUser = Auth::user();

        // Admin service ne peut modifier que les employés de son service
        if ($currentUser->isAdminService() && !$currentUser->isSuperAdmin()) {
            if ($user->service_id !== $currentUser->service_id) {
                abort(403, 'Cet utilisateur n\'appartient pas à votre service.');
            }
        }

        $roles = $currentUser->isSuperAdmin()
            ? Role::pluck('name', 'name')
            : Role::whereIn('name', ['employe', 'admin_service'])->pluck('name', 'name');

        $services = Service::pluck('nom', 'id');

        return view('users.edit', compact('user', 'roles', 'services'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();
        $currentUser = Auth::user();

        // Admin service ne peut modifier que les employés de son service
        if ($currentUser->isAdminService() && !$currentUser->isSuperAdmin()) {
            if ($user->service_id !== $currentUser->service_id) {
                abort(403, 'Cet utilisateur n\'appartient pas à votre service.');
            }
        }

        $allowedRoles = $currentUser->isSuperAdmin()
            ? ['super_admin', 'admin_reseau', 'admin_service', 'employe']
            : ['employe', 'admin_service'];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'fonction' => ['nullable', 'string', 'max:100'],
            'est_actif' => ['nullable', 'boolean'],
            'role' => ['required', 'in:' . implode(',', $allowedRoles)],
            'service_id' => ['nullable', 'exists:services,id'],
        ]);

        $serviceId = $currentUser->isAdminService() && !$currentUser->isSuperAdmin()
            ? $currentUser->service_id
            : $request->service_id;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'fonction' => $request->fonction,
            'est_actif' => $request->filled('est_actif'),
            'service_id' => $serviceId,
        ]);

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $user)
    {
        $this->ensureAdmin();
        $currentUser = Auth::user();

        if ($currentUser->isAdminService() && !$currentUser->isSuperAdmin()) {
            if ($user->service_id !== $currentUser->service_id) {
                abort(403, 'Cet utilisateur n\'appartient pas à votre service.');
            }
        }

        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}
