<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;
use App\Models\User;

echo "Création/ récupération du service...\n";
$service = Service::firstOrCreate(
    ['nom' => 'Service Informatique'],
    ['description' => 'Service IT - Administration des accès WiFi', 'est_actif' => true]
);
echo "Service ID: " . $service->id . "\n";

echo "Recherche de l'utilisateur admin-service...\n";
$user = User::where('email', 'admin-service@example.com')->first();

if ($user) {
    echo "Utilisateur trouvé: " . $user->name . " (ID: " . $user->id . ")\n";
    echo "Ancien service_id: " . ($user->service_id ?? 'NULL') . "\n";
    
    $user->service_id = $service->id;
    $user->save();
    
    $service->responsable_id = $user->id;
    $service->save();
    
    echo "Nouveau service_id: " . $user->service_id . "\n";
    echo "✓ Service assigné avec succès!\n";
} else {
    echo "✗ Utilisateur admin-service@example.com non trouvé\n";
}
