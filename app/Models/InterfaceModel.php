<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterfaceModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'routeur_id',
        'nom',
        'type',
        'adresse_mac',
        'adresse_ip',
        'mask',
        'vlan_id',
        'parent_interface',
        'statut',
        'est_active',
        'rx_bytes',
        'tx_bytes',
        'rx_packets',
        'tx_packets',
        'rx_errors',
        'tx_errors',
        'rx_drops',
        'tx_drops',
        'debit_entrant',
        'debit_sortant',
        'ssid',
        'bande',
        'canal',
        'puissance_signal',
        'clients_connectes',
        'description',
        'configuration',
    ];

    protected $casts = [
        'est_active' => 'boolean',
        'configuration' => 'array',
        'debit_entrant' => 'float',
        'debit_sortant' => 'float',
        'clients_connectes' => 'integer',
        'puissance_signal' => 'integer',
        'canal' => 'integer',
        'vlan_id' => 'integer',
    ];

    public function routeur()
    {
        return $this->belongsTo(Routeur::class);
    }
}
