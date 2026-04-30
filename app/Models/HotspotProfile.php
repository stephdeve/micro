<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotspotProfile extends Model
{
    protected $fillable = [
        'routeur_id',
        'nom',
        'shared_users',
        'rate_limit',
        'session_timeout',
        'idle_timeout',
        'keepalive_timeout',
        'status_autorefresh',
        'mac_cookie_timeout',
        'transparent_proxy',
        'radius_accounting',
        'open_status_page',
        'advertise',
        'advertise_timeout',
        'advertise_url',
        'active',
        'commentaire'
    ];

    protected $casts = [
        'shared_users' => 'integer',
        'transparent_proxy' => 'boolean',
        'radius_accounting' => 'boolean',
        'open_status_page' => 'boolean',
        'advertise' => 'boolean',
        'active' => 'boolean',
    ];

    public function routeur(): BelongsTo
    {
        return $this->belongsTo(Routeur::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(HotspotUser::class, 'profile_id');
    }

    public function rateLimitFormatted(): string
    {
        if (!$this->rate_limit) {
            return 'Illimité';
        }
        return $this->rate_limit;
    }

    public function sessionTimeoutFormatted(): string
    {
        if (!$this->session_timeout) {
            return 'Illimité';
        }
        return $this->session_timeout;
    }
}
