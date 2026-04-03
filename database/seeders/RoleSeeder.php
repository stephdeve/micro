<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
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

        // Création des deux rôles uniquement
        Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        Role::firstOrCreate([
            'name' => 'employe',
            'guard_name' => 'web'
        ]);

        $this->command->info('✓ Rôles admin et employe créés avec succès');
    }
}