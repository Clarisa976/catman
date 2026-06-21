<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookMetadata\BookLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookLookupController extends Controller
{
    public function __construct(private readonly BookLookupService $lookup)
    {
    }

    public function isbn(string $isbn): JsonResponse
    {
        try {
            return response()->json([
                'data' => $this->lookup->lookupByIsbn($isbn),
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            return response()->json([
                'data' => $this->lookup->searchByTitle($data['query'], $data),
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function import(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'original_title' => ['nullable', 'string', 'max:255'],
            'volume_title' => ['nullable', 'string', 'max:255'],
            'authors' => ['nullable', 'array'],
            'authors.*' => ['string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'published_date' => ['nullable', 'string', 'max:80'],
            'language' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'isbn_10' => ['nullable', 'string', 'max:32'],
            'isbn_13' => ['nullable', 'string', 'max:32'],
            'ean' => ['nullable', 'string', 'max:32'],
            'page_count' => ['nullable', 'integer', 'min:0'],
            'cover_url' => ['nullable', 'url', 'max:2048'],
            'description' => ['nullable', 'string'],
            'provider' => ['nullable', 'string', 'max:80'],
            'provider_id' => ['nullable', 'string', 'max:255'],
            'provider_url' => ['nullable', 'url', 'max:2048'],
            'raw_data' => ['nullable', 'array'],
            'volume_number' => ['nullable', 'numeric', 'min:0'],
            'work_type' => ['nullable', 'string', 'in:manga,manhwa,manhua,novel,other'],
            'add_to_collection' => ['sometimes', 'boolean'],
            'ownership_status' => ['nullable', 'string', 'in:owned,wishlist,reserved,pending,not_interested,sold,lent'],
            'reading_status' => ['nullable', 'string', 'in:not_started,reading,read,paused'],
            'is_travel_memory' => ['sometimes', 'boolean'],
            'purchase_country' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            return response()->json($this->lookup->import($data, $request->user()->id), 201);
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
