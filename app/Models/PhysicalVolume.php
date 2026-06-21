<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhysicalVolume extends Model
{
    protected $fillable = [
        'work_id',
        'volume_number',
        'title',
        'language',
        'country',
        'publisher',
        'edition_name',
        'isbn',
        'ean',
        'release_date',
        'price',
        'currency',
        'cover_url',
        'metadata_source',
        'metadata_provider_id',
        'metadata_url',
        'metadata_fetched_at',
        'raw_metadata',
    ];

    protected function casts(): array
    {
        return [
            'volume_number' => 'decimal:2',
            'release_date' => 'date',
            'price' => 'decimal:2',
            'metadata_fetched_at' => 'datetime',
            'raw_metadata' => 'array',
        ];
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function userPhysicalCollection(): HasMany
    {
        return $this->hasMany(UserPhysicalCollection::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }
}
