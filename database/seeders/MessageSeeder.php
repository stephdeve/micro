<?php

namespace Database\Seeders;

use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Message::factory(100)->create(); // 100 messages fictifs
    }
}