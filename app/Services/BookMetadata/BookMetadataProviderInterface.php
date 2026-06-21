<?php

namespace App\Services\BookMetadata;

interface BookMetadataProviderInterface
{
    public function name(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function lookupByIsbn(string $isbn): array;

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchByTitle(string $query, array $filters = []): array;
}
