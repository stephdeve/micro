<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Crypt;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'conversation_id',
        'subject',
        'encrypted_content',
        'encryption_iv',
        'encryption_key_hash',
        'is_group_message',
        'read_at',
        'is_encrypted',
        'message_type',
    ];

    protected $casts = [
        'is_group_message' => 'boolean',
        'is_encrypted' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Clé de chiffrement AES-256 (à stocker de manière sécurisée)
    private static ?string $encryptionKey = null;

    public static function getEncryptionKey(): string
    {
        if (self::$encryptionKey === null) {
            $key = env('MESSAGE_ENCRYPTION_KEY') ?? env('APP_KEY') ?? 'default-encryption-key-32chars-long';
            self::$encryptionKey = $key;
        }
        return self::$encryptionKey;
    }

    /**
     * Chiffrer un message avec AES-256
     */
    public static function encrypt(string $content): array
    {
        $iv = random_bytes(16); // IV de 16 bytes pour AES-256-CBC
        $key = hash('sha256', self::getEncryptionKey(), true);
        
        $encrypted = openssl_encrypt($content, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        return [
            'content' => base64_encode($encrypted),
            'iv' => base64_encode($iv),
            'key_hash' => hash('sha256', $key),
        ];
    }

    /**
     * Déchiffrer le message
     */
    public function decrypt(): ?string
    {
        if (!$this->is_encrypted || !$this->encrypted_content) {
            return $this->encrypted_content;
        }

        $key = hash('sha256', self::getEncryptionKey(), true);
        $iv = base64_decode($this->encryption_iv);
        $encrypted = base64_decode($this->encrypted_content);
        
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        return $decrypted !== false ? $decrypted : null;
    }

    /**
     * Récupérer le contenu déchiffré (accessor)
     */
    public function getDecryptedContentAttribute(): ?string
    {
        return $this->decrypt();
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'message_recipients')
            ->withPivot('read_at', 'is_starred', 'is_archived')
            ->withTimestamps();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function recipientsList(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }

    /**
     * Marquer comme lu pour un utilisateur
     */
    public function markAsRead(int $userId): void
    {
        $this->recipients()->updateExistingPivot($userId, ['read_at' => now()]);
    }

    /**
     * Vérifier si lu par un utilisateur
     */
    public function isReadBy(int $userId): bool
    {
        return $this->recipientsList()
            ->where('user_id', $userId)
            ->whereNotNull('read_at')
            ->exists();
    }

    /**
     * Nombre de destinataires non lus
     */
    public function unreadCount(): int
    {
        return $this->recipientsList()
            ->whereNull('read_at')
            ->count();
    }
}
