<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les permissions
        $permissions = [
            'manage_routers', 'manage_interfaces', 'manage_firewall', 'manage_wifi',
            'manage_bandwidth', 'manage_routes', 'view_network',
            'manage_employees', 'manage_profiles', 'view_team_stats',
            'view_own_traffic', 'use_messaging',
            'manage_all_services', 'manage_all_users', 'view_global_infrastructure',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Créer les 4 rôles
        $roles = [
            'super_admin' => Permission::pluck('name')->toArray(),
            'admin_reseau' => [
                'manage_routers', 'manage_interfaces', 'manage_firewall', 'manage_wifi',
                'manage_bandwidth', 'manage_routes', 'view_network', 'use_messaging',
            ],
            'admin_service' => [
                'manage_employees', 'manage_profiles', 'view_team_stats', 'use_messaging',
            ],
            'employe' => [
                'view_own_traffic', 'use_messaging',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        // Créer les services
        $services = [
            ['nom' => 'Direction Générale', 'code' => 'DG', 'description' => 'Direction générale de l\'entreprise'],
            ['nom' => 'Service Informatique', 'code' => 'IT', 'description' => 'Gestion du système d\'information'],
            ['nom' => 'Service Réseau', 'code' => 'NET', 'description' => 'Administration réseau et télécoms'],
            ['nom' => 'Service Administratif', 'code' => 'ADM', 'description' => 'Gestion administrative'],
            ['nom' => 'Service Comptabilité', 'code' => 'CPT', 'description' => 'Gestion comptable et financière'],
        ];

        foreach ($services as $svc) {
            Service::firstOrCreate(['code' => $svc['code']], $svc);
        }

        // Créer l'utilisateur Super Admin
        $itService = Service::where('code', 'IT')->first();

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@micro.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'telephone' => '+213 000 000 001',
                'fonction' => 'Super Administrateur',
                'est_actif' => true,
                'service_id' => $itService?->id,
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Créer l'Admin Réseau
        $netService = Service::where('code', 'NET')->first();

        $adminReseau = User::firstOrCreate(
            ['email' => 'adminreseau@micro.local'],
            [
                'name' => 'Admin Réseau',
                'password' => Hash::make('password'),
                'telephone' => '+213 000 000 002',
                'fonction' => 'Administrateur Réseau',
                'est_actif' => true,
                'service_id' => $netService?->id,
            ]
        );
        $adminReseau->assignRole('admin_reseau');

        // Créer l'Admin Service
        $admService = Service::where('code', 'ADM')->first();

        $adminService = User::firstOrCreate(
            ['email' => 'adminservice@micro.local'],
            [
                'name' => 'Admin Service',
                'password' => Hash::make('password'),
                'telephone' => '+213 000 000 003',
                'fonction' => 'Administrateur de Service',
                'est_actif' => true,
                'service_id' => $admService?->id,
            ]
        );
        $adminService->assignRole('admin_service');
        // Le lier comme responsable de son service
        if ($admService) {
            $admService->update(['responsable_id' => $adminService->id]);
        }

        // Créer un Employé
        $employe = User::firstOrCreate(
            ['email' => 'employe@micro.local'],
            [
                'name' => 'Employé Test',
                'password' => Hash::make('password'),
                'telephone' => '+213 000 000 004',
                'fonction' => 'Employé',
                'est_actif' => true,
                'service_id' => $admService?->id,
            ]
        );
        $employe->assignRole('employe');
    }
}
