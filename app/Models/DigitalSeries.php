<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalSeries extends Model
{
    protected $fillable = [
        'work_id',
        'platform_id',
        'title',
        'platform_url',
        'language',
        'status',
        'latest_episode_detected',
        'last_checked_at',
        'cover_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latest_episode_detected' => 'decimal:2',
            'last_checked_at' => 'datetime',
        ];
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(DigitalPlatform::class, 'platform_id');
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(DigitalEpisode::class);
    }

    public function userDigitalTrackings(): HasMany
    {
        return $this->hasMany(UserDigitalTracking::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
