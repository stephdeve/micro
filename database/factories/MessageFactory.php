<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        $isSecure = $this->faker->boolean(30); // 30% de messages chiffrés
        $content = $this->faker->paragraph();

        return [
            'sender_id' => \App\Models\User::factory(),
            'receiver_id' => \App\Models\User::factory(),
            'subject' => $this->faker->sentence(),
            'content' => $isSecure ? Crypt::encryptString($content) : $content,
            'priority' => $this->faker->randomElement(['basse', 'normale', 'haute', 'urgente']),
            'is_secure' => $isSecure,
            'is_read' => $this->faker->boolean(70), // 70% lus
            'is_starred' => $this->faker->boolean(20), // 20% favoris
            'folder' => $this->faker->randomElement(['inbox', 'sent', 'archive', 'trash']),
            'has_attachments' => $this->faker->boolean(15), // 15% avec pièces jointes
            'read_at' => $this->faker->optional(0.7)->dateTime(), // 70% ont une date de lecture
        ];
    }
}