<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametre extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cle',
        'valeur',
        'type',
        'groupe',
        'libelle',
        'description',
        'est_modifiable',
        'est_visible',
        'options',
    ];

    protected $casts = [
        'est_modifiable' => 'boolean',
        'est_visible' => 'boolean',
        'options' => 'array',
    ];
}
