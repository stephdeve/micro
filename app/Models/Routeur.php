<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function responsable()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}