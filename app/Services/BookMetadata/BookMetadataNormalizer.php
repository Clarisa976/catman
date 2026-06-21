<?php

namespace App\Services\BookMetadata;

use Illuminate\Support\Arr;

class BookMetadataNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public static function normalize(array $data, string $provider): array
    {
        $isbn10 = self::cleanNullableCode($data['isbn_10'] ?? null);
        $isbn13 = self::cleanNullableCode($data['isbn_13'] ?? null);
        $ean = self::cleanNullableCode($data['ean'] ?? $isbn13);

        return [
            'title' => self::nullableString($data['title'] ?? null),
            'original_title' => self::nullableString($data['original_title'] ?? null),
            'volume_title' => self::nullableString($data['volume_title'] ?? null),
            'authors' => self::normalizeAuthors($data['authors'] ?? []),
            'publisher' => self::nullableString($data['publisher'] ?? null),
            'published_date' => self::nullableString($data['published_date'] ?? null),
            'language' => self::nullableString($data['language'] ?? null),
            'country' => self::nullableString($data['country'] ?? null),
            'isbn_10' => $isbn10,
            'isbn_13' => $isbn13,
            'ean' => $ean,
            'page_count' => isset($data['page_count']) ? (int) $data['page_count'] : null,
            'cover_url' => self::nullableString($data['cover_url'] ?? null),
            'description' => self::nullableString($data['description'] ?? null),
            'provider' => self::nullableString($data['provider'] ?? null) ?? $provider,
            'provider_id' => self::nullableString($data['provider_id'] ?? null),
            'provider_url' => self::nullableString($data['provider_url'] ?? null),
            'raw_data' => $data['raw_data'] ?? $data,
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function normalizeAuthors(mixed $authors): array
    {
        return collect(Arr::wrap($authors))
            ->map(function (mixed $author): ?string {
                if (is_array($author)) {
                    return self::nullableString($author['name'] ?? $author['key'] ?? null);
                }

                return self::nullableString($author);
            })
            ->filter()
            ->values()
            ->all();
    }

    private static function cleanNullableCode(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return IsbnCode::normalize((string) $value);
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
