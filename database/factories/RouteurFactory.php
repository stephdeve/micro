<?php

namespace Database\Factories;

use App\Models\Routeur;
use Illuminate\Database\Eloquent\Factories\Factory;

class RouteurFactory extends Factory
{
    protected $model = Routeur::class;

    public function definition()
    {
        return [
            'nom' => $this->faker->word,
            'modele' => $this->faker->randomElement(['RB750Gr3', 'hAP ac', 'CCR1009']),
            'adresse_ip' => $this->faker->unique()->ipv4,
            'adresse_mac' => $this->faker->unique()->macAddress,
            'version_ros' => '7.12',
            'firmware' => '7.12.1',
            'numero_serie' => $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'statut' => $this->faker->randomElement(['en_ligne', 'hors_ligne', 'maintenance']),
            'emplacement' => $this->faker->city,
            'description' => $this->faker->sentence,
            'user_id' => \App\Models\User::factory(),
        ];
    }
}