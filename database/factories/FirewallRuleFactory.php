<?php

namespace Database\Factories;

use App\Models\FirewallRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class FirewallRuleFactory extends Factory
{
    protected $model = FirewallRule::class;

    public function definition()
    {
        return [
            'routeur_id' => \App\Models\Routeur::factory(),
            'numero_ordre' => $this->faker->numberBetween(1, 100),
            'nom' => $this->faker->word(),
            'action' => $this->faker->randomElement(['accept', 'drop', 'reject', 'jump', 'log']),
            'chain' => $this->faker->randomElement(['input', 'output', 'forward', 'prerouting', 'postrouting']),
            'protocole' => $this->faker->randomElement(['tcp', 'udp', 'icmp', null]),
            'src_address' => $this->faker->optional()->ipv4(),
            'dst_address' => $this->faker->optional()->ipv4(),
            'src_port' => $this->faker->optional()->numberBetween(1, 65535),
            'dst_port' => $this->faker->optional()->numberBetween(1, 65535),
            'est_active' => $this->faker->boolean(80),
            'description' => $this->faker->optional()->sentence(),
            'configuration_complete' => json_encode(['test' => 'config']),
        ];
    }
}