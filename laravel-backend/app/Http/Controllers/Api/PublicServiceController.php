<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ← ini yang kamu butuh


class PublicServiceController extends Controller
{
    public function index() {
        return response()->json(PublicService::all());
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:service_categories,id',
            'description' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'location' => 'required',
            'opening_hours' => 'nullable|json',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean'
        ]);

        $service = PublicService::create($validated);
        return response()->json($service, 201);
    }

    public function show($id) {
        return response()->json(PublicService::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $service = PublicService::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:service_categories,id',
            'description' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'location' => 'nullable',
            'opening_hours' => 'nullable|json',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean'
        ]);

        $service->update($validated);
        return response()->json($service);
    }

    public function destroy($id) {
        $service = PublicService::findOrFail($id);
        $service->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function getInZone($search_id){
        $services = DB::table('public_services')
            ->join('walkability_zones', DB::raw('ST_Within(public_services.location, walkability_zones.zone_polygon)'), '=', DB::raw('true'))
            ->join('service_categories', 'public_services.category_id', '=', 'service_categories.id')
            ->where('walkability_zones.search_id', $search_id)
            ->select(
                'public_services.id',
                'public_services.name',
                'public_services.description',
                DB::raw('ST_Y(location::geometry) as lat'),
                DB::raw('ST_X(location::geometry) as lng'),
                'public_services.category_id',
                'service_categories.name as category_name'
            )
            ->get();

        return response()->json($services);
    }

}
