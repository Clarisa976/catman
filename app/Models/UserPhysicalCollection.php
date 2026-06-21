<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPhysicalCollection extends Model
{
    protected $table = 'user_physical_collections';

    protected $fillable = [
        'user_id',
        'physical_volume_id',
        'ownership_status',
        'reading_status',
        'purchase_date',
        'purchase_price',
        'purchase_currency',
        'store',
        'is_travel_memory',
        'purchase_country',
        'location',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_price' => 'decimal:2',
            'is_travel_memory' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function physicalVolume(): BelongsTo
    {
        return $this->belongsTo(PhysicalVolume::class);
    }
}
