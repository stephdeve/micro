<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WifiZone extends Model
{
    protected $fillable = [
        'routeur_id', 'nom', 'ssid', 'password', 'security_profile',
        'bandwidth_down', 'bandwidth_up', 'quota_monthly', 'vlan_id',
        'schedule_start', 'schedule_end', 'schedule_days',
        'client_isolation', 'max_clients', 'frequency_band',
        'wifi_interface_name', 'active', 'commentaire'
    ];

    protected $casts = [
        'schedule_days' => 'array',
        'client_isolation' => 'boolean',
        'active' => 'boolean',
        'quota_monthly' => 'integer',
    ];

    public function routeur(): BelongsTo
    {
        return $this->belongsTo(Routeur::class);
    }

    public function employes(): HasMany
    {
        return $this->hasMany(Employe::class);
    }

    public function isUnlimited(): bool
    {
        return $this->quota_monthly === 0;
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
        $down = $this->bandwidth_down > 0 ? $this->bandwidth_down . ' Mbps' : 'Illimité';
        $up = $this->bandwidth_up > 0 ? $this->bandwidth_up . ' Mbps' : 'Illimité';
        return "↓ $down / ↑ $up";
    }

    public function scheduleFormatted(): ?string
    {
        if (!$this->schedule_start || !$this->schedule_end) {
            return null;
        }

        $days = '';
        if ($this->schedule_days) {
            $dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
            $selected = array_map(fn($d) => $dayNames[$d] ?? $d, $this->schedule_days);
            $days = ' (' . implode(', ', $selected) . ')';
        }

        return $this->schedule_start . ' - ' . $this->schedule_end . $days;
    }
}
