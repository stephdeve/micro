<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Commandes MikroTik
Artisan::command('mikrotik:commands', function () {
    $this->info('Commandes MikroTik disponibles:');
    $this->line('  mikrotik:test {ip} {user} {password} [--port=8728]     Tester la connexion');
    $this->line('  mikrotik:add-router {nom} {ip} {user} {password}      Ajouter un routeur');
    $this->line('  mikrotik:monitor [--router=] [--all]                   Surveiller les routeurs');
})->purpose('Lister les commandes MikroTik');
