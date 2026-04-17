<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'file_hash',
        'is_encrypted',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_encrypted' => 'boolean',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Formater la taille du fichier
     */
    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Obtenir l'icône selon le type MIME
     */
    public function icon(): string
    {
        return match(true) {
            str_contains($this->mime_type, 'image') => 'fa-image',
            str_contains($this->mime_type, 'pdf') => 'fa-file-pdf',
            str_contains($this->mime_type, 'word') => 'fa-file-word',
            str_contains($this->mime_type, 'excel') || str_contains($this->mime_type, 'sheet') => 'fa-file-excel',
            str_contains($this->mime_type, 'powerpoint') || str_contains($this->mime_type, 'presentation') => 'fa-file-powerpoint',
            str_contains($this->mime_type, 'zip') || str_contains($this->mime_type, 'rar') || str_contains($this->mime_type, '7z') => 'fa-file-archive',
            str_contains($this->mime_type, 'text') => 'fa-file-alt',
            default => 'fa-file',
        };
    }
}
