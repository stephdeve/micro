<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Routeur;
use App\Services\MikrotikService;

class MikroTikTestConnection extends Command
{
    protected $signature = 'mikrotik:test {ip} {user} {password} {--port=8728}';
    protected $description = 'Tester la connexion à un routeur MikroTik';

    public function handle(): int
    {
        $ip = $this->argument('ip');
        $user = $this->argument('user');
        $password = $this->argument('password');
        $port = $this->option('port');

        $this->info("Test de connexion à {$ip}:{$port}...");

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

            $this->info("✓ Connexion réussie!");
            $this->info("  Identité: {$identity}");

            // Récupérer les ressources système
            $resResp = $client->sendSync(new \PEAR2\Net\RouterOS\Request('/system/resource/print'));
            foreach ($resResp as $item) {
                if ($item instanceof \PEAR2\Net\RouterOS\Response) {
                    $this->info("  Version: " . $item->getProperty('version'));
                    $this->info("  Uptime: " . $item->getProperty('uptime'));
                    $this->info("  CPU Load: " . $item->getProperty('cpu-load') . '%');
                    $this->info("  Mémoire libre: " . $this->formatBytes($item->getProperty('free-memory')));
                    break;
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("✗ Échec de connexion: {$e->getMessage()}");
            return 1;
        }
    }

    private function formatBytes($bytes): string
    {
        $bytes = (int) $bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}
