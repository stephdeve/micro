<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Routeur;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\Auth;

class MikroTikAddRouter extends Command
{
    protected $signature = 'mikrotik:add-router 
                            {nom : Nom du routeur}
                            {ip : Adresse IP du routeur}
                            {api_user : Nom d\'utilisateur API}
                            {api_password : Mot de passe API}
                            {--port=8728 : Port API}
                            {--model= : Modèle du routeur}
                            {--location= : Emplacement physique}';
    
    protected $description = 'Ajouter un routeur MikroTik réel à la base de données';

    public function handle(): int
    {
        $nom = $this->argument('nom');
        $ip = $this->argument('ip');
        $user = $this->argument('api_user');
        $password = $this->argument('api_password');
        $port = $this->option('port');

        $this->info("Test de connexion au routeur {$ip}:{$port}...");

        // Tester la connexion avant d'ajouter
        try {
            $client = new \PEAR2\Net\RouterOS\Client($ip, $user, $password, (int)$port);
            $response = $client->sendSync(new \PEAR2\Net\RouterOS\Request('/system/identity/print'));
            
            $identity = null;
            foreach ($response as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $identity = $item->getProperty('name');
                    break;
                }
            }
            $this->info("✓ Connexion réussie! Identité: {$identity}");
        } catch (\Exception $e) {
            $this->error("✗ Impossible de se connecter: {$e->getMessage()}");
            if (!$this->confirm('Ajouter quand même?')) {
                return 1;
            }
        }

        // Créer le routeur
        $routeur = Routeur::create([
            'nom' => $nom,
            'adresse_ip' => $ip,
            'api_user' => $user,
            'api_password' => $password,
            'modele' => $this->option('model'),
            'emplacement' => $this->option('location'),
            'statut' => 'en_ligne',
            'derniere_connexion' => now(),
            'user_id' => 1, // Super admin par défaut
        ]);

        $this->info("✓ Routeur ajouté avec succès! ID: {$routeur->id}");
        
        // Lancer la synchronisation initiale si connexion OK
        if (isset($client)) {
            $this->info("Synchronisation des données...");
            $service = app(MikrotikService::class);
            $service->handshake($routeur);
            $service->discoverInterfaces($routeur);
            $this->info("✓ Synchronisation terminée");
        }

        return 0;
    }
}
