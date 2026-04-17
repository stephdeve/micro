<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Création de tous les rôles
        $roles = [
            'super_admin',
            'admin_reseau',
            'admin_service',
            'employe',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
        }

        // Création des permissions
        $permissions = [
            'manage_employees',
            'view_stats',
            'manage_wifi',
            'view_employees',
            'use_messaging',
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web'
            ]);
        }

        // Assigner permissions au rôle admin_service
        $adminServiceRole = Role::findByName('admin_service');
        $adminServiceRole->givePermissionTo(['manage_employees', 'view_stats', 'manage_wifi', 'view_employees', 'use_messaging']);

        // Assigner toutes les permissions au super_admin
        $superAdminRole = Role::findByName('super_admin');
        $superAdminRole->givePermissionTo(Permission::all());

        $this->command->info('✓ Rôles créés avec succès: ' . implode(', ', $roles));
        $this->command->info('✓ Permissions assignées');
    }
}