<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DigitalPlatformResource;
use App\Models\DigitalPlatform;
use Illuminate\Http\Request;

class DigitalPlatformController extends Controller
{
    public function index()
    {
        return DigitalPlatformResource::collection(DigitalPlatform::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:digital_platforms,name'],
            'website_url' => ['nullable', 'url', 'max:2048'],
        ]);

        return (new DigitalPlatformResource(DigitalPlatform::create($data)))
            ->response()
            ->setStatusCode(201);
    }

    public function show(DigitalPlatform $digitalPlatform)
    {
        return new DigitalPlatformResource($digitalPlatform->load('digitalSeries'));
    }

    public function update(Request $request, DigitalPlatform $digitalPlatform)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:digital_platforms,name,'.$digitalPlatform->id],
            'website_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $digitalPlatform->update($data);

        return new DigitalPlatformResource($digitalPlatform->fresh());
    }

    public function destroy(DigitalPlatform $digitalPlatform)
    {
        $digitalPlatform->delete();

        return response()->noContent();
    }
}
