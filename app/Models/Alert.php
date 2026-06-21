<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'user_id',
        'alert_type',
        'work_id',
        'physical_volume_id',
        'digital_series_id',
        'digital_episode_id',
        'title',
        'message',
        'status',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function physicalVolume(): BelongsTo
    {
        return $this->belongsTo(PhysicalVolume::class);
    }

    public function digitalSeries(): BelongsTo
    {
        return $this->belongsTo(DigitalSeries::class);
    }

    public function digitalEpisode(): BelongsTo
    {
        return $this->belongsTo(DigitalEpisode::class);
    }
}
