<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirewallRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'routeur_id',
        'numero_ordre',
        'nom',
        'action',
        'chain',
        'protocole',
        'src_address',
        'dst_address',
        'src_port',
        'dst_port',
        'in_interface',
        'out_interface',
        'connection_state',
        'est_active',
        'description',
        'configuration_complete',
        'compteur_paquets',
        'compteur_octets',
    ];

    protected $casts = [
        'est_active' => 'boolean',
        'configuration_complete' => 'array',
    ];

    public function routeur()
    {
        return $this->belongsTo(Routeur::class);
    }
}
