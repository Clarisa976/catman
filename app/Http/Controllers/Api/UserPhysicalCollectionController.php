<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserPhysicalCollectionResource;
use App\Models\UserPhysicalCollection;
use Illuminate\Http\Request;

class UserPhysicalCollectionController extends Controller
{
    public function index(Request $request)
    {
        $items = UserPhysicalCollection::with('physicalVolume.work')
            ->where('user_id', $request->user()->id)
            ->when($request->query('ownership_status'), fn ($query, $value) => $query->where('ownership_status', $value))
            ->when($request->query('reading_status'), fn ($query, $value) => $query->where('reading_status', $value))
            ->when($request->has('is_travel_memory'), fn ($query) => $query->where('is_travel_memory', $request->boolean('is_travel_memory')))
            ->when($request->query('language'), fn ($query, $value) => $query->whereHas('physicalVolume', fn ($query) => $query->where('language', $value)))
            ->when($request->query('country'), fn ($query, $value) => $query->whereHas('physicalVolume', fn ($query) => $query->where('country', $value)))
            ->when($request->query('search'), function ($query, $value): void {
                $query->whereHas('physicalVolume', function ($query) use ($value): void {
                    $query
                        ->where('title', 'like', '%'.$value.'%')
                        ->orWhere('isbn', 'like', '%'.$value.'%')
                        ->orWhere('ean', 'like', '%'.$value.'%')
                        ->orWhereHas('work', fn ($query) => $query->where('title', 'like', '%'.$value.'%'));
                });
            })
            ->latest()
            ->paginate($this->perPage($request));

        return UserPhysicalCollectionResource::collection($items);
    }

    public function store(Request $request)
    {
        $item = UserPhysicalCollection::create($this->validateItem($request) + [
            'user_id' => $request->user()->id,
        ]);

        return (new UserPhysicalCollectionResource($item->load('physicalVolume.work')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(Request $request, UserPhysicalCollection $item)
    {
        abort_unless($item->user_id === $request->user()->id, 404);

        $item->update($this->validateItem($request, true));

        return new UserPhysicalCollectionResource($item->fresh('physicalVolume.work'));
    }

    public function destroy(Request $request, UserPhysicalCollection $item)
    {
        abort_unless($item->user_id === $request->user()->id, 404);

        $item->delete();

        return response()->noContent();
    }

    private function validateItem(Request $request, bool $partial = false): array
    {
        return $request->validate([
            'physical_volume_id' => [$partial ? 'sometimes' : 'required', 'exists:physical_volumes,id'],
            'ownership_status' => ['sometimes', 'string', 'in:owned,wishlist,reserved,pending,not_interested,sold,lent'],
            'reading_status' => ['sometimes', 'string', 'in:not_started,reading,read,paused'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_currency' => ['sometimes', 'string', 'size:3'],
            'store' => ['nullable', 'string', 'max:255'],
            'is_travel_memory' => ['sometimes', 'boolean'],
            'purchase_country' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
