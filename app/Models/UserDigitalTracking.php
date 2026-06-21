<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDigitalTracking extends Model
{
    protected $fillable = [
        'user_id',
        'digital_series_id',
        'follow_updates',
        'notify_new_episode',
        'reading_status',
        'last_episode_read',
        'last_episode_seen',
        'started_at',
        'finished_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'follow_updates' => 'boolean',
            'notify_new_episode' => 'boolean',
            'last_episode_read' => 'decimal:2',
            'last_episode_seen' => 'decimal:2',
            'started_at' => 'date',
            'finished_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function digitalSeries(): BelongsTo
    {
        return $this->belongsTo(DigitalSeries::class);
    }
}
