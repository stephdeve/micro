<?php

namespace Database\Seeders;

use App\Models\Parametre;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParametreSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Paramètres spécifiques (updateOrCreate pour éviter doublons)
        Parametre::updateOrCreate(
            ['cle' => 'app_name'],
            [
                'valeur' => 'NetAdmin MikroTik',
                'type' => 'string',
                'groupe' => 'general',
                'libelle' => 'Nom de l\'application',
                'description' => 'Nom affiché dans l\'interface',
                'est_modifiable' => true,
            ]
        );

        Parametre::updateOrCreate(
            ['cle' => 'theme'],
            [
                'valeur' => 'dark',
                'type' => 'string',
                'groupe' => 'general',
                'libelle' => 'Thème',
                'description' => 'Thème de l\'interface (light/dark)',
                'est_modifiable' => true,
                'options' => json_encode(['light', 'dark']),
            ]
        );

        Parametre::updateOrCreate(
            ['cle' => 'debug_mode'],
            [
                'valeur' => 'false',
                'type' => 'boolean',
                'groupe' => 'general',
                'libelle' => 'Mode debug',
                'description' => 'Activer le mode debug',
                'est_modifiable' => false,
            ]
        );

        // Quelques paramètres supplémentaires
        Parametre::updateOrCreate(
            ['cle' => 'max_users'],
            [
                'valeur' => '100',
                'type' => 'integer',
                'groupe' => 'general',
                'libelle' => 'Nombre max d\'utilisateurs',
                'est_modifiable' => true,
            ]
        );

        Parametre::updateOrCreate(
            ['cle' => 'session_timeout'],
            [
                'valeur' => '3600',
                'type' => 'integer',
                'groupe' => 'securite',
                'libelle' => 'Timeout de session (secondes)',
                'est_modifiable' => true,
            ]
        );
    }
}