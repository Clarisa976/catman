<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserPhysicalCollection;
use Illuminate\Http\Request;

class UserPhysicalCollectionController extends Controller
{
    public function index(Request $request)
    {
        return UserPhysicalCollection::with('physicalVolume.work')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate();
    }

    public function store(Request $request)
    {
        $item = UserPhysicalCollection::create($this->validateItem($request) + [
            'user_id' => $request->user()->id,
        ]);

        return response()->json($item->load('physicalVolume.work'), 201);
    }

    public function update(Request $request, UserPhysicalCollection $item)
    {
        abort_unless($item->user_id === $request->user()->id, 404);

        $item->update($this->validateItem($request, true));

        return $item->fresh('physicalVolume.work');
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
