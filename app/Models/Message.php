<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'content',
        'priority',
        'is_secure',
        'is_read',
        'is_starred',
        'folder',
        'tag',
        'has_attachments',
        'read_at',
    ];

    protected $casts = [
        'is_secure' => 'boolean',
        'is_read' => 'boolean',
        'is_starred' => 'boolean',
        'has_attachments' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachement::class);
    }
}
