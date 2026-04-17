<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'code',
        'description',
        'est_actif',
        'responsable_id',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function employes()
    {
        return $this->hasMany(User::class, 'service_id');
    }

    public function adminsService()
    {
        return $this->employes()->whereHas('roles', function ($q) {
            $q->where('name', 'admin_service');
        });
    }
}
