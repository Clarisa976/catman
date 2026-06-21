<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWorkTracking extends Model
{
    protected $fillable = [
        'user_id',
        'work_id',
        'follow_physical_releases',
        'preferred_language',
        'preferred_country',
        'preferred_publisher',
        'last_owned_volume_number',
        'notify_new_volume',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'follow_physical_releases' => 'boolean',
            'last_owned_volume_number' => 'decimal:2',
            'notify_new_volume' => 'boolean',
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
}
