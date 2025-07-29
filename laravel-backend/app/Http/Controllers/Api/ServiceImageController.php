<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceImage;
use Illuminate\Http\Request;

class ServiceImageController extends Controller
{
    public function index() {
        return response()->json(ServiceImage::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'service_id' => 'required|exists:public_services,id',
            'image_url' => 'required|string|max:500',
            'image_alt' => 'nullable|string',
            'is_primary' => 'boolean'
        ]);

        $image = ServiceImage::create($validated);
        return response()->json($image, 201);
    }

    public function show($id) {
        return response()->json(ServiceImage::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $image = ServiceImage::findOrFail($id);

        $validated = $request->validate([
            'service_id' => 'sometimes|required|exists:public_services,id',
            'image_url' => 'nullable|string|max:500',
            'image_alt' => 'nullable|string',
            'is_primary' => 'boolean'
        ]);

        $image->update($validated);
        return response()->json($image);
    }

    public function destroy($id) {
        $image = ServiceImage::findOrFail($id);
        $image->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
