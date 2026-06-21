<?php

namespace App\Services\BookMetadata;

use App\Models\BookMetadataLookup;
use App\Models\PhysicalVolume;
use App\Models\Work;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookLookupService
{
    /**
     * @param iterable<BookMetadataProviderInterface> $providers
     */
    public function __construct(private readonly iterable $providers)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function lookupByIsbn(string $isbn): array
    {
        $isbn = IsbnCode::normalizeAndValidate($isbn);

        return $this->runProviders('isbn', $isbn, fn (BookMetadataProviderInterface $provider) => $provider->lookupByIsbn($isbn));
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function searchByTitle(string $query, array $filters = []): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            throw new InvalidArgumentException('Search query must contain at least 2 characters.');
        }

        return $this->runProviders('title', mb_strtolower($query), fn (BookMetadataProviderInterface $provider) => $provider->searchByTitle($query, $filters));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function import(array $payload, int $userId): array
    {
        return DB::transaction(function () use ($payload, $userId): array {
            $payload = $this->normalizeImportPayload($payload);
            $author = collect($payload['authors'] ?? [])->first();

            $work = Work::query()
                ->where('title', $payload['title'])
                ->when($author, fn ($query) => $query->where('author', $author))
                ->first();

            if (! $work) {
                $work = Work::create([
                    'title' => $payload['title'],
                    'original_title' => $payload['original_title'] ?? null,
                    'author' => $author,
                    'cover_url' => $payload['cover_url'] ?? null,
                    'type' => $payload['work_type'] ?? 'manga',
                    'status' => 'unknown',
                ]);
            }

            $volume = $this->findExistingVolume($work, $payload);

            if (! $volume) {
                $volume = PhysicalVolume::create([
                    'work_id' => $work->id,
                    'volume_number' => $payload['volume_number'] ?? 1,
                    'title' => $payload['volume_title'] ?? null,
                    'language' => $payload['language'] ?? 'espanol',
                    'country' => $payload['country'] ?? 'Espana',
                    'publisher' => $payload['publisher'] ?? null,
                    'isbn' => $payload['isbn'] ?? null,
                    'ean' => $payload['ean'] ?? null,
                    'release_date' => $this->normalizeDate($payload['published_date'] ?? null),
                    'cover_url' => $payload['cover_url'] ?? null,
                    'metadata_source' => $payload['provider'] ?? 'manual',
                    'metadata_provider_id' => $payload['provider_id'] ?? null,
                    'metadata_url' => $payload['provider_url'] ?? null,
                    'metadata_fetched_at' => now(),
                    'raw_metadata' => $payload['raw_data'] ?? null,
                ]);
            }

            $collectionItem = null;

            if ($payload['add_to_collection'] ?? false) {
                $collectionItem = $volume->userPhysicalCollection()->firstOrCreate(
                    [
                        'user_id' => $userId,
                    ],
                    [
                        'ownership_status' => $payload['ownership_status'] ?? 'owned',
                        'reading_status' => $payload['reading_status'] ?? 'not_started',
                        'is_travel_memory' => (bool) ($payload['is_travel_memory'] ?? false),
                        'purchase_country' => $payload['purchase_country'] ?? null,
                    ],
                );
            }

            return [
                'work' => $work->fresh(),
                'physical_volume' => $volume->fresh('work'),
                'collection_item' => $collectionItem?->fresh('physicalVolume'),
            ];
        });
    }

    private function normalizeDate(?string $value): ?string
    {
        if (! $value || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }

    /**
     * @param callable(BookMetadataProviderInterface): array<int, array<string, mixed>> $callback
     *
     * @return array<int, array<string, mixed>>
     */
    private function runProviders(string $lookupType, string $lookupValue, callable $callback): array
    {
        return Collection::make($this->providers)
            ->flatMap(function (BookMetadataProviderInterface $provider) use ($lookupType, $lookupValue, $callback): array {
                $cached = BookMetadataLookup::query()
                    ->where('provider', $provider->name())
                    ->where('lookup_type', $lookupType)
                    ->where('lookup_value', $lookupValue)
                    ->where('fetched_at', '>=', Carbon::now()->subDay())
                    ->first();

                if ($cached) {
                    return $cached->success ? ($cached->normalized_result ?? []) : [];
                }

                try {
                    $results = collect($callback($provider))
                        ->map(fn (array $result): array => BookMetadataNormalizer::normalize($result, $provider->name()))
                        ->values()
                        ->all();

                    BookMetadataLookup::create([
                        'provider' => $provider->name(),
                        'lookup_type' => $lookupType,
                        'lookup_value' => $lookupValue,
                        'normalized_result' => $results,
                        'raw_response' => collect($results)->pluck('raw_data')->all(),
                        'success' => true,
                        'fetched_at' => now(),
                    ]);

                    return $results;
                } catch (\Throwable $exception) {
                    BookMetadataLookup::create([
                        'provider' => $provider->name(),
                        'lookup_type' => $lookupType,
                        'lookup_value' => $lookupValue,
                        'success' => false,
                        'error_message' => $exception->getMessage(),
                        'fetched_at' => now(),
                    ]);

                    return [];
                }
            })
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function normalizeImportPayload(array $payload): array
    {
        foreach (['isbn_10', 'isbn_13', 'ean'] as $key) {
            if (! empty($payload[$key])) {
                $payload[$key] = IsbnCode::normalizeAndValidate((string) $payload[$key]);
            }
        }

        $payload['isbn'] = $payload['isbn_13'] ?? $payload['isbn_10'] ?? null;
        $payload['ean'] = $payload['ean'] ?? $payload['isbn_13'] ?? null;

        return $payload;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function findExistingVolume(Work $work, array $payload): ?PhysicalVolume
    {
        $isbn = $payload['isbn'] ?? null;
        $ean = $payload['ean'] ?? null;

        if ($isbn || $ean) {
            $volume = PhysicalVolume::query()
                ->where(function ($query) use ($isbn, $ean): void {
                    $query
                        ->when($isbn, fn ($query) => $query->orWhere('isbn', $isbn))
                        ->when($ean, fn ($query) => $query->orWhere('ean', $ean));
                })
                ->first();

            if ($volume) {
                return $volume;
            }
        }

        return PhysicalVolume::query()
            ->where('work_id', $work->id)
            ->where('volume_number', $payload['volume_number'] ?? 1)
            ->where('language', $payload['language'] ?? 'espanol')
            ->where('country', $payload['country'] ?? 'Espana')
            ->where('publisher', $payload['publisher'] ?? null)
            ->where('edition_name', $payload['edition_name'] ?? null)
            ->first();
    }
}
