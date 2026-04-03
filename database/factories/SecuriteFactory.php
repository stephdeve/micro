<?php

namespace Database\Factories;

use App\Models\Securite;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecuriteFactory extends Factory
{
    protected $model = Securite::class;

    public function definition()
    {
        return [
            'nom_evenement' => $this->faker->word(),
            'type' => $this->faker->randomElement(['intrusion', 'tentative_connexion', 'alerte_firewall', 'mise_a_jour', 'scan_port', 'ddos', 'autre']),
            'severite' => $this->faker->randomElement(['info', 'faible', 'moyenne', 'haute', 'critique']),
            'statut' => $this->faker->randomElement(['nouveau', 'en_cours', 'resolu', 'ignore']),
            'source_ip' => $this->faker->ipv4(),
            'description' => $this->faker->sentence(),
            'donnees_brutes' => json_encode(['test' => 'data']),
        ];
    }
}