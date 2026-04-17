<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Routeur;
use App\Services\MikrotikService;

class MikroTikMonitor extends Command
{
    protected $signature = 'mikrotik:monitor {--router=} {--all}';
    protected $description = 'Surveiller et synchroniser les routeurs MikroTik';

    public function handle(MikrotikService $service): int
    {
        if ($this->option('all')) {
            $routeurs = Routeur::all();
        } elseif ($this->option('router')) {
            $routeurs = Routeur::where('id', $this->option('router'))->get();
        } else {
            $routeurs = Routeur::where('statut', 'en_ligne')->get();
        }

        if ($routeurs->isEmpty()) {
            $this->warn('Aucun routeur à surveiller.');
            return 1;
        }

        foreach ($routeurs as $routeur) {
            $this->info("Routeur: {$routeur->nom} ({$routeur->adresse_ip})");
            
            $connected = $service->testConnection($routeur);
            
            if ($connected) {
                $this->info('  ✓ Connecté');
                $routeur->update(['statut' => 'en_ligne', 'derniere_connexion' => now()]);
                
                // Synchroniser les données
                $service->handshake($routeur);
                $service->discoverInterfaces($routeur);
                
                $this->info('  ✓ Synchronisé');
            } else {
                $this->error('  ✗ Hors ligne');
                $routeur->update(['statut' => 'hors_ligne']);
            }
        }

        return 0;
    }
}
