<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Securite extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom_evenement',
        'type',
        'severite',
        'statut',
        'source_ip',
        'destination_ip',
        'port_source',
        'port_destination',
        'protocole',
        'routeur_id',
        'interface_id',
        'description',
        'action_entreprise',
        'donnees_brutes',
        'compteur',
        'est_bloque',
        'resolu_a',
        'resolu_par',
    ];

    protected $casts = [
        'donnees_brutes' => 'array',
        'est_bloque' => 'boolean',
        'resolu_a' => 'datetime',
    ];

    public function routeur()
    {
        return $this->belongsTo(Routeur::class);
    }

    public function interfaceModel()
    {
        return $this->belongsTo(InterfaceModel::class, 'interface_id');
    }
}
