<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalEpisode extends Model
{
    protected $fillable = [
        'digital_series_id',
        'episode_number',
        'title',
        'release_date',
        'episode_url',
        'is_free',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'episode_number' => 'decimal:2',
            'release_date' => 'date',
            'is_free' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    public function digitalSeries(): BelongsTo
    {
        return $this->belongsTo(DigitalSeries::class);
    }

    public function userDigitalEpisodeReads(): HasMany
    {
        return $this->hasMany(UserDigitalEpisodeRead::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
