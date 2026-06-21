<?php

namespace App\Services\BookMetadata;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class OpenLibraryMetadataProvider implements BookMetadataProviderInterface
{
    public function name(): string
    {
        return 'open_library';
    }

    public function lookupByIsbn(string $isbn): array
    {
        $response = Http::timeout(8)->get('https://openlibrary.org/isbn/'.$isbn.'.json');

        if ($response->failed()) {
            return [];
        }

        $book = $response->json();

        return [$this->normalizeBook($book, $isbn)];
    }

    public function searchByTitle(string $query, array $filters = []): array
    {
        $response = Http::timeout(8)->get('https://openlibrary.org/search.json', array_filter([
            'title' => $query,
            'author' => $filters['author'] ?? null,
            'publisher' => $filters['publisher'] ?? null,
            'language' => $filters['language'] ?? null,
            'limit' => 10,
        ]));

        if ($response->failed()) {
            return [];
        }

        return collect($response->json('docs', []))
            ->map(fn (array $book): array => $this->normalizeSearchResult($book))
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $book
     *
     * @return array<string, mixed>
     */
    private function normalizeBook(array $book, string $isbn): array
    {
        $isbn13 = Arr::first($book['isbn_13'] ?? [], fn ($value) => $value === $isbn) ?? ($book['isbn_13'][0] ?? null);
        $isbn10 = Arr::first($book['isbn_10'] ?? [], fn ($value) => $value === $isbn) ?? ($book['isbn_10'][0] ?? null);
        $coverId = $book['covers'][0] ?? null;

        return [
            'title' => $book['title'] ?? null,
            'original_title' => null,
            'volume_title' => $book['subtitle'] ?? null,
            'authors' => $book['authors'] ?? [],
            'publisher' => $book['publishers'][0] ?? null,
            'published_date' => $book['publish_date'] ?? null,
            'language' => $book['languages'][0]['key'] ?? null,
            'country' => null,
            'isbn_10' => $isbn10,
            'isbn_13' => $isbn13,
            'ean' => strlen($isbn) === 13 ? $isbn : null,
            'page_count' => $book['number_of_pages'] ?? null,
            'cover_url' => $coverId ? 'https://covers.openlibrary.org/b/id/'.$coverId.'-L.jpg' : null,
            'description' => is_array($book['description'] ?? null) ? ($book['description']['value'] ?? null) : ($book['description'] ?? null),
            'provider' => $this->name(),
            'provider_id' => $book['key'] ?? null,
            'provider_url' => isset($book['key']) ? 'https://openlibrary.org'.$book['key'] : null,
            'raw_data' => $book,
        ];
    }

    /**
     * @param array<string, mixed> $book
     *
     * @return array<string, mixed>
     */
    private function normalizeSearchResult(array $book): array
    {
        $isbn13 = collect($book['isbn'] ?? [])->first(fn ($isbn): bool => strlen((string) $isbn) === 13);
        $isbn10 = collect($book['isbn'] ?? [])->first(fn ($isbn): bool => strlen((string) $isbn) === 10);
        $coverId = $book['cover_i'] ?? null;

        return [
            'title' => $book['title'] ?? null,
            'original_title' => null,
            'volume_title' => $book['subtitle'] ?? null,
            'authors' => $book['author_name'] ?? [],
            'publisher' => $book['publisher'][0] ?? null,
            'published_date' => isset($book['first_publish_year']) ? (string) $book['first_publish_year'] : null,
            'language' => $book['language'][0] ?? null,
            'country' => null,
            'isbn_10' => $isbn10,
            'isbn_13' => $isbn13,
            'ean' => $isbn13,
            'page_count' => null,
            'cover_url' => $coverId ? 'https://covers.openlibrary.org/b/id/'.$coverId.'-L.jpg' : null,
            'description' => null,
            'provider' => $this->name(),
            'provider_id' => $book['key'] ?? null,
            'provider_url' => isset($book['key']) ? 'https://openlibrary.org'.$book['key'] : null,
            'raw_data' => $book,
        ];
    }
}
