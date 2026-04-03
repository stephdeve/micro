<?php

namespace Database\Seeders;

use App\Models\Securite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SecuriteSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Securite::factory(20)->create(); // 20 événements sécurité fictifs
    }
}