<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index()
    {
        return response()->json(District::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'district_code' => 'nullable|string|max:10',
            'area_km2' => 'nullable|numeric',
            'population' => 'nullable|integer',
            'population_density' => 'nullable|numeric',
            'boundary_polygon' => 'nullable', // tipe geometry, bisa WKT/GeoJSON
            'center_point' => 'nullable',     // tipe point, bisa WKT/GeoJSON
        ]);

        $district = District::create($validated);

        return response()->json($district, 201);
    }

    public function show($id)
    {
        $district = District::findOrFail($id);
        return response()->json($district);
    }

    public function update(Request $request, $id)
    {
        $district = District::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'district_code' => 'nullable|string|max:10',
            'area_km2' => 'nullable|numeric',
            'population' => 'nullable|integer',
            'population_density' => 'nullable|numeric',
            'boundary_polygon' => 'nullable',
            'center_point' => 'nullable',
        ]);

        $district->update($validated);

        return response()->json($district);
    }

    public function destroy($id)
    {
        $district = District::findOrFail($id);
        $district->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
