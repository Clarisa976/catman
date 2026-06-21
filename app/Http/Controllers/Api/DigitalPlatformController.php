<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalPlatform;
use Illuminate\Http\Request;

class DigitalPlatformController extends Controller
{
    public function index()
    {
        return DigitalPlatform::orderBy('name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:digital_platforms,name'],
            'website_url' => ['nullable', 'url', 'max:2048'],
        ]);

        return response()->json(DigitalPlatform::create($data), 201);
    }

    public function show(DigitalPlatform $digitalPlatform)
    {
        return $digitalPlatform->load('digitalSeries');
    }

    public function update(Request $request, DigitalPlatform $digitalPlatform)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:digital_platforms,name,'.$digitalPlatform->id],
            'website_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $digitalPlatform->update($data);

        return $digitalPlatform->fresh();
    }

    public function destroy(DigitalPlatform $digitalPlatform)
    {
        $digitalPlatform->delete();

        return response()->noContent();
    }
}
