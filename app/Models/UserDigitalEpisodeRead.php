<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDigitalEpisodeRead extends Model
{
    protected $fillable = [
        'user_id',
        'digital_episode_id',
        'read_status',
        'read_at',
        'notes',
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

    public function digitalEpisode(): BelongsTo
    {
        return $this->belongsTo(DigitalEpisode::class);
    }
}
