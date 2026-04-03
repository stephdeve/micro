<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MikrotikService;

class RouteursHeartbeat extends Command
{
    protected $signature = 'routeurs:heartbeat';
    protected $description = 'Exécute le heartbeat pour tous les routeurs MikroTik';

    public function handle()
    {
        $service = app(MikrotikService::class);
        $service->captureHeartbeat();

        $this->info('Heartbeat exécuté.');
    }
}
