<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalPlatform extends Model
{
    protected $fillable = [
        'name',
        'website_url',
    ];

    public function digitalSeries(): HasMany
    {
        return $this->hasMany(DigitalSeries::class, 'platform_id');
    }
}
