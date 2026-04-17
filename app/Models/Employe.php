<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employe extends Model
{
    protected $fillable = [
        'routeur_id', 'wifi_zone_id', 'user_id', 'nom', 'prenom', 'email',
        'telephone', 'matricule', 'departement', 'poste', 'mac_address', 'ip_address',
        'bandwidth_down', 'bandwidth_up', 'quota_monthly', 'data_used_this_month',
        'data_used_total', 'last_connected_at', 'connection_duration_minutes',
        'active', 'notes'
    ];

    protected $casts = [
        'active' => 'boolean',
        'quota_monthly' => 'integer',
        'data_used_this_month' => 'integer',
        'data_used_total' => 'integer',
        'bandwidth_down' => 'integer',
        'bandwidth_up' => 'integer',
        'last_connected_at' => 'datetime',
    ];

    public function routeur(): BelongsTo
    {
        return $this->belongsTo(Routeur::class);
    }

    public function wifiZone(): BelongsTo
    {
        return $this->belongsTo(WifiZone::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullName(): string
    {
        return $this->prenom . ' ' . $this->nom;
    }

    public function isUnlimited(): bool
    {
        return $this->quota_monthly === 0;
    }

    public function quotaRemaining(): int
    {
        if ($this->isUnlimited()) {
            return PHP_INT_MAX;
        }
        return max(0, $this->quota_monthly - $data_used_this_month);
    }

    public function quotaUsedPercent(): float
    {
        if ($this->isUnlimited() || $this->quota_monthly === 0) {
            return 0;
        }
        return min(100, ($this->data_used_this_month / $this->quota_monthly) * 100);
    }

    public function quotaFormatted(): string
    {
        if ($this->isUnlimited()) {
            return 'Illimité';
        }
        if ($this->quota_monthly >= 1024) {
            return round($this->quota_monthly / 1024, 1) . ' Go';
        }
        return $this->quota_monthly . ' Mo';
    }

    public function bandwidthFormatted(): string
    {
        // Si personnalisé, utiliser les valeurs de l'employé, sinon hériter de la zone
        $down = $this->bandwidth_down;
        $up = $this->bandwidth_up;

        if ($down === 0 && $this->wifiZone) {
            $down = $this->wifiZone->bandwidth_down;
        }
        if ($up === 0 && $this->wifiZone) {
            $up = $this->wifiZone->bandwidth_up;
        }

        $downStr = $down > 0 ? $down . ' Mbps' : 'Illimité';
        $upStr = $up > 0 ? $up . ' Mbps' : 'Illimité';
        return "↓ $downStr / ↑ $upStr";
    }

    public function dataUsedFormatted(): string
    {
        if ($this->data_used_this_month >= 1024) {
            return round($this->data_used_this_month / 1024, 2) . ' Go';
        }
        return $this->data_used_this_month . ' Mo';
    }

    public function connectionDurationFormatted(): string
    {
        $hours = floor($this->connection_duration_minutes / 60);
        $minutes = $this->connection_duration_minutes % 60;
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'min';
        }
        return $minutes . ' min';
    }

    public function statusBadge(): string
    {
        if (!$this->active) {
            return '<span class="badge badge-danger">Bloqué</span>';
        }
        if ($this->last_connected_at && $this->last_connected_at->gt(now()->subMinutes(5))) {
            return '<span class="badge badge-success">En ligne</span>';
        }
        return '<span class="badge badge-secondary">Hors ligne</span>';
    }
}
