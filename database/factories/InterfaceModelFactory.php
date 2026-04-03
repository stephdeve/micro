<?php

namespace Database\Factories;

use App\Models\InterfaceModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterfaceModelFactory extends Factory
{
    protected $model = InterfaceModel::class;

    public function definition()
    {
        return [
            'routeur_id' => \App\Models\Routeur::factory(),
            'nom' => $this->faker->randomElement(['ether1', 'ether2', 'ether3', 'wlan1', 'bridge1']),
            'type' => $this->faker->randomElement(['ethernet', 'wireless', 'bridge', 'vlan']),
            'adresse_mac' => $this->faker->unique()->macAddress,
            'statut' => $this->faker->randomElement(['actif', 'inactif']),
            'debit_entrant' => $this->faker->numberBetween(0, 1000),
            'debit_sortant' => $this->faker->numberBetween(0, 1000),
            'clients_connectes' => $this->faker->numberBetween(0, 50),
        ];
    }
}