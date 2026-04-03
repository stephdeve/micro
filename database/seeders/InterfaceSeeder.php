<?php

namespace Database\Seeders;

use App\Models\InterfaceModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterfaceSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        InterfaceModel::factory(50)->create(); // 50 interfaces fictives
    }
}