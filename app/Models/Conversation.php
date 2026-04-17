<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
        'is_group',
        'avatar',
    ];

    protected $casts = [
        'is_group' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany('created_at');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_members')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    /**
     * Obtenir le nom d'affichage pour un utilisateur spécifique
     */
    public function displayName(int $userId): string
    {
        if ($this->is_group) {
            return $this->name ?? 'Groupe sans nom';
        }

        // Pour une conversation privée, retourner le nom de l'autre participant
        $otherMember = $this->members()->where('users.id', '!=', $userId)->first();
        return $otherMember ? $otherMember->name : 'Conversation';
    }

    /**
     * Nombre de messages non lus pour un utilisateur
     */
    public function unreadCount(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereDoesntHave('recipients', function ($query) use ($userId) {
                $query->where('user_id', $userId)->whereNotNull('read_at');
            })
            ->count();
    }
}
