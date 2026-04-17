<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageRecipient extends Model
{
    use HasFactory;

    protected $table = 'message_recipients';

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
        'is_starred',
        'is_archived',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'is_starred' => 'boolean',
        'is_archived' => 'boolean',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
