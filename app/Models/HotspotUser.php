<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotspotUser extends Model
{
    protected $fillable = [
        'routeur_id',
        'profile_id',
        'username',
        'password',
        'mac_address',
        'email',
        'telephone',
        'nom_complet',
        'type', // 'voucher', 'employe', 'invite', 'permanent'
        'data_limit',
        'time_limit',
        'valid_from',
        'valid_until',
        'commentaire',
        'disabled',
        'last_login',
        'total_uptime',
        'bytes_in',
        'bytes_out',
        'mikrotik_id'
    ];

    protected $casts = [
        'disabled' => 'boolean',
        'data_limit' => 'integer',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'last_login' => 'datetime',
        'bytes_in' => 'integer',
        'bytes_out' => 'integer',
    ];

    public function routeur(): BelongsTo
    {
        return $this->belongsTo(Routeur::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(HotspotProfile::class, 'profile_id');
    }

    public function isActive(): bool
    {
        if ($this->disabled) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }

    public function statusBadge(): string
    {
        if (!$this->isActive()) {
            return '<span class="px-2 py-0.5 rounded-full text-xs bg-rose-500/20 text-rose-400 border border-rose-500/30">Inactif</span>';
        }

        if ($this->last_login) {
            return '<span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Connecté</span>';
        }

        return '<span class="px-2 py-0.5 rounded-full text-xs bg-blue-500/20 text-blue-400 border border-blue-500/30">Actif</span>';
    }

    public function dataUsedFormatted(): string
    {
        $total = ($this->bytes_in + $this->bytes_out) / (1024 * 1024); // MB
        if ($total < 1024) {
            return round($total, 2) . ' MB';
        }
        return round($total / 1024, 2) . ' GB';
    }

    public function dataLimitFormatted(): string
    {
        if (!$this->data_limit) {
            return 'Illimité';
        }
        if ($this->data_limit < 1024) {
            return $this->data_limit . ' MB';
        }
        return round($this->data_limit / 1024, 2) . ' GB';
    }

    public function remainingData(): ?float
    {
        if (!$this->data_limit) {
            return null;
        }
        $used = ($this->bytes_in + $this->bytes_out) / (1024 * 1024);
        return max(0, $this->data_limit - $used);
    }
}
