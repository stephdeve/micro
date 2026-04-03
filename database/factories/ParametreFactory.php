<?php

namespace Database\Factories;

use App\Models\Parametre;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParametreFactory extends Factory
{
    protected $model = Parametre::class;

    public function definition()
    {
        return [
            'cle' => $this->faker->randomElement(['theme', 'notifications_email', 'notifications_push', 'langue', 'timezone', 'app_name', 'debug_mode', 'max_users', 'session_timeout']),
            'valeur' => $this->faker->randomElement(['light', 'dark', 'true', 'false', 'fr', 'en', 'UTC', 'Europe/Paris', 'NetAdmin', '1', '100', '3600']),
            'type' => $this->faker->randomElement(['string', 'boolean', 'integer']),
            'groupe' => $this->faker->randomElement(['general', 'reseau', 'securite', 'notifications']),
            'libelle' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'est_modifiable' => $this->faker->boolean(90),
            'est_visible' => $this->faker->boolean(95),
        ];
    }
}