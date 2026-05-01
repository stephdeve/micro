<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BandwidthProfile extends Model
{
    use HasFactory;

    protected $table = 'bandwidth_profiles';

    protected $fillable = [
        'routeur_id',
        'nom',
        'description',
        'download_mbps',
        'upload_mbps',
        'quota_gb',
        'target_network',
        'priority',
        'active',
        'color',
    ];

    protected $casts = [
        'download_mbps' => 'integer',
        'upload_mbps' => 'integer',
        'quota_gb' => 'integer',
        'priority' => 'integer',
        'active' => 'boolean',
    ];

    public function routeur()
    {
        return $this->belongsTo(Routeur::class);
    }

    /**
     * Formater la bande passante pour affichage
     */
    public function bandwidthFormatted(): string
    {
        $down = $this->download_mbps >= 1000 
            ? ($this->download_mbps / 1000) . ' Gbps' 
            : $this->download_mbps . ' Mbps';
        $up = $this->upload_mbps >= 1000 
            ? ($this->upload_mbps / 1000) . ' Gbps' 
            : $this->upload_mbps . ' Mbps';
        return $down . ' ↓ / ' . $up . ' ↑';
    }

    /**
     * Formater le quota pour affichage
     */
    public function quotaFormatted(): string
    {
        if ($this->quota_gb === 0 || $this->quota_gb === null) {
            return 'Illimité';
        }
        if ($this->quota_gb >= 1000) {
            return ($this->quota_gb / 1000) . ' To';
        }
        return $this->quota_gb . ' Go';
    }

    /**
     * Générer le format MikroTik max-limit
     */
    public function getMikrotikMaxLimit(): string
    {
        $up = $this->upload_mbps > 0 ? $this->upload_mbps . 'M' : '0';
        $down = $this->download_mbps > 0 ? $this->download_mbps . 'M' : '0';
        return $up . '/' . $down;
    }

    /**
     * Générer un nom de queue unique pour MikroTik
     */
    public function getQueueName(): string
    {
        return 'profil_' . str_replace(' ', '_', strtolower($this->nom)) . '_' . $this->id;
    }
}
