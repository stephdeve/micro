<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; 

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles; 

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telephone',
        'fonction',
        'est_actif',
        'derniere_connexion',
        'avatar',
        'service_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function messagesEnvoyes()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messagesRecus()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function isActif(): bool
    {
        return $this->est_actif;
    }

    public function isAdminReseau(): bool
    {
        return $this->hasRole('admin_reseau');
    }

    public function isAdminService(): bool
    {
        return $this->hasRole('admin_service');
    }

    public function isEmploye(): bool
    {
        return $this->hasRole('employe');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }
}
