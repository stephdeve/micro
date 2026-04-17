<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\MikrotikService;

class Routeur extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'modele',
        'adresse_ip',
        'adresse_mac',
        'version_ros',
        'firmware',
        'numero_serie',
        'statut',
        'uptime',
        'cpu_usage',
        'memory_usage',
        'temperature',
        'emplacement',
        'description',
        'configuration',
        'derniere_connexion',
        'derniere_sync',
        'api_user',
        'api_password',
        'user_id'
    ];

    protected $casts = [
        'configuration' => 'array',
        'derniere_connexion' => 'datetime',
        'derniere_sync' => 'datetime',
        'cpu_usage' => 'float',
        'memory_usage' => 'float',
        'temperature' => 'float',
        'uptime' => 'integer'
    ];

    public function interfaces()
    {
        return $this->hasMany(InterfaceModel::class);
    }

    public function wifiZones()
    {
        return $this->hasMany(WifiZone::class);
    }

    public function employes()
    {
        return $this->hasMany(Employe::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Handshake avec le routeur MikroTik - récupère les infos système et interfaces
     */
    public function handshake(): array
    {
        try {
            $service = app(\App\Services\MikrotikService::class);

            // Utiliser la méthode handshake existante du service (avec timeout)
            $success = retry(1, function () use ($service) {
                return $service->handshake($this);
            }, 5000); // 5 secondes max

            if ($success) {
                // Récupérer aussi les adresses IP des interfaces
                try {
                    $client = $service->client($this);
                    $ipResp = $client->sendSync(new \PEAR2\Net\RouterOS\Request('/ip/address/print'));
                    foreach ($ipResp as $item) {
                        if (!$item instanceof \PEAR2\Net\RouterOS\Response) continue;
                        $ifaceName = $item->getProperty('interface');
                        if ($ifaceName) {
                            $interface = $this->interfaces()->where('nom', $ifaceName)->first();
                            if ($interface) {
                                $interface->update(['adresse_ip' => $item->getProperty('address')]);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning("IP addresses fetch failed for {$this->nom}: " . $e->getMessage());
                }

                return ['success' => true, 'message' => 'Routeur synchronisé avec succès'];
            }

            return ['success' => false, 'error' => 'Handshake échoué - routeur injoignable'];

        } catch (\Throwable $e) {
            $this->update(['statut' => 'hors_ligne', 'derniere_sync' => now()]);
            \Log::error("Handshake error for router {$this->nom}: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}