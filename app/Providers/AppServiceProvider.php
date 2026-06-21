<?php

namespace App\Providers;

use App\Services\BookMetadata\BookLookupService;
use App\Services\BookMetadata\ManualMetadataProvider;
use App\Services\BookMetadata\OpenLibraryMetadataProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BookLookupService::class, function (): BookLookupService {
            $providers = collect(config('book_metadata.providers', []))
                ->map(fn (string $provider): object => match ($provider) {
                    'open_library' => new OpenLibraryMetadataProvider(),
                    'manual' => new ManualMetadataProvider(),
                    default => new ManualMetadataProvider(),
                })
                ->all();

            return new BookLookupService($providers);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
