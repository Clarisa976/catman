<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookMetadataLookup extends Model
{
    protected $fillable = [
        'provider',
        'lookup_type',
        'lookup_value',
        'normalized_result',
        'raw_response',
        'success',
        'error_message',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'normalized_result' => 'array',
            'raw_response' => 'array',
            'success' => 'boolean',
            'fetched_at' => 'datetime',
        ];
    }
}
