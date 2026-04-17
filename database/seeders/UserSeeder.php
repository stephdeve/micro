<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création de l'administrateur
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
                'est_actif' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('super_admin');

        // Création admin réseau
        $adminReseau = User::firstOrCreate(
            ['email' => 'admin-reseau@example.com'],
            [
                'name' => 'Administrateur Réseau',
                'password' => Hash::make('password'),
                'est_actif' => true,
                'email_verified_at' => now(),
            ]
        );
        $adminReseau->assignRole('admin_reseau');

        // Création d'un service pour l'admin service
        $service = Service::firstOrCreate(
            ['nom' => 'Service Informatique'],
            [
                'code' => 'INFO',
                'description' => 'Service IT - Administration des accès WiFi',
                'est_actif' => true,
            ]
        );

        // Création admin service
        $adminService = User::firstOrCreate(
            ['email' => 'admin-service@example.com'],
            [
                'name' => 'Administrateur Service',
                'password' => Hash::make('password'),
                'est_actif' => true,
                'email_verified_at' => now(),
                'service_id' => $service->id,
            ]
        );
        $adminService->assignRole('admin_service');

        // Mettre à jour le responsable du service
        $service->responsable_id = $adminService->id;
        $service->save();

        // Création d'un employé
        $employe1 = User::firstOrCreate(
            ['email' => 'employe1@example.com'],
            [
                'name' => 'Jean Dupont',
                'password' => Hash::make('password'),
                'est_actif' => true,
                'email_verified_at' => now(),
            ]
        );
        $employe1->assignRole('employe');

        $this->command->info('✓ Utilisateurs créés avec succès');
        $this->command->info('   admin@example.com / password       -> super_admin');
        $this->command->info('   admin-reseau@example.com / password -> admin_reseau');
        $this->command->info('   admin-service@example.com / password  -> admin_service');
        $this->command->info('   employe1@example.com / password     -> employe');
    }
}