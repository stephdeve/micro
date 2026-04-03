<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageAttachement extends Model
{
    use HasFactory;

    protected $table = 'message_attachments';

    protected $fillable = [
        'message_id',
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'file_size',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}
