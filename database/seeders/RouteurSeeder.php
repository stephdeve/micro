<?php

namespace Database\Seeders;

use App\Models\Routeur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RouteurSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Routeur::factory(10)->create(); // 10 routeurs fictifs
    }
}