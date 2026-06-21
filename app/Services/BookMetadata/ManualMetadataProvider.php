<?php

namespace App\Services\BookMetadata;

class ManualMetadataProvider implements BookMetadataProviderInterface
{
    public function name(): string
    {
        return 'manual';
    }

    public function lookupByIsbn(string $isbn): array
    {
        return [];
    }

    public function searchByTitle(string $query, array $filters = []): array
    {
        return [];
    }
}
