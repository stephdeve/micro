<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
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
        $admin->assignRole('admin');

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
        $this->command->info('   admin@example.com / password');
        $this->command->info('   employe1@example.com / password');
    }
}