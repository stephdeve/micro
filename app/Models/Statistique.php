<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistique extends Model
{
    use HasFactory;

    protected $fillable = [
        'routeur_id',
        'interface_id',
        'timestamp',
        'type',
        'valeur',
        'unite',
        'donnees_complementaires',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'donnees_complementaires' => 'array',
        'valeur' => 'float',
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
