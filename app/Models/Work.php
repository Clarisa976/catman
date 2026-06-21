<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Work extends Model
{
    protected $fillable = [
        'title',
        'original_title',
        'author',
        'type',
        'status',
        'total_volumes',
        'cover_url',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_volumes' => 'integer',
        ];
    }

    public function physicalVolumes(): HasMany
    {
        return $this->hasMany(PhysicalVolume::class);
    }

    public function userWorkTrackings(): HasMany
    {
        return $this->hasMany(UserWorkTracking::class);
    }

    public function digitalSeries(): HasMany
    {
        return $this->hasMany(DigitalSeries::class);
    }
}
