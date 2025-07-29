<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function index() {
        return response()->json(ServiceCategory::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|max:7'
        ]);

        $category = ServiceCategory::create($validated);
        return response()->json($category, 201);
    }

    public function show($id) {
        return response()->json(ServiceCategory::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $category = ServiceCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string|max:7'
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy($id) {
        $category = ServiceCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
