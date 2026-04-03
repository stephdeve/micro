<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        if (!Auth::user() || !Auth::user()->hasRole('admin')) {
            abort(403, 'Accès non autorisé.');
        }
    }

    public function index()
    {
        $this->ensureAdmin();
        $users = User::orderBy('name')->paginate(5);
        $roles = Role::pluck('name', 'name');

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->ensureAdmin();
        $roles = Role::pluck('name', 'name');
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'fonction' => ['nullable', 'string', 'max:100'],
            'est_actif' => ['nullable', 'boolean'],
            'role' => ['required', 'in:admin,employe'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telephone' => $request->telephone,
            'fonction' => $request->fonction,
            'est_actif' => $request->filled('est_actif'),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        $this->ensureAdmin();
        $roles = Role::pluck('name', 'name');
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'fonction' => ['nullable', 'string', 'max:100'],
            'est_actif' => ['nullable', 'boolean'],
            'role' => ['required', 'in:admin,employe'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'fonction' => $request->fonction,
            'est_actif' => $request->filled('est_actif'),
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

        if (Auth::id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }
}
