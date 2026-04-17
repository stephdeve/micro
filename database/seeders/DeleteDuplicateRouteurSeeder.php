<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Routeur;

class DeleteDuplicateRouteurSeeder extends Seeder
{
    public function run(): void
    {
        // Delete all routeurs with IP 192.168.88.1 (including soft deleted)
        $routeurs = Routeur::withTrashed()->where('adresse_ip', '192.168.88.1')->get();
        
        foreach ($routeurs as $routeur) {
            $routeur->forceDelete();
        }
        
        echo "Deleted " . $routeurs->count() . " routeur(s) with IP 192.168.88.1\n";
    }
}
