<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WalkabilityZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalkabilityZoneController extends Controller
{
    public function index() {
        return response()->json(WalkabilityZone::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'search_id' => 'required|exists:user_searches,id',
            'polygon' => 'required|array',
            'zone_type' => 'required|string',
            'travel_time' => 'required|integer',
        ]);

        $polygon = $request->polygon;

        if (!isset($polygon['coordinates'][0]) || count($polygon['coordinates'][0]) < 3) {
            return response()->json(['error' => 'Invalid polygon format.'], 422);
        }

        $polygonText = 'POLYGON((' . collect($polygon['coordinates'][0])
            ->map(fn ($coord) => "{$coord[0]} {$coord[1]}") // lng lat
            ->implode(', ') . '))';

        DB::table('walkability_zones')->insert([
            'search_id' => $request->search_id,
            'zone_polygon' => DB::raw("ST_GeomFromText('$polygonText', 4326)"),
            'zone_type' => $request->zone_type,
            'travel_time' => $request->travel_time,
        ]);

        return response()->json(['message' => 'Zone saved.'], 201);
    }

    public function show($id) {
        return response()->json(WalkabilityZone::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $zone = WalkabilityZone::findOrFail($id);

        $validated = $request->validate([
            'search_id' => 'sometimes|required|exists:user_searches,id',
            'zone_polygon' => 'nullable',
            'zone_type' => 'nullable|string|max:50',
            'travel_time' => 'nullable|integer'
        ]);

        $zone->update($validated);
        return response()->json($zone);
    }

    public function destroy($id) {
        $zone = WalkabilityZone::findOrFail($id);
        $zone->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function check(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        // Toleransi 10 meter dalam derajat (sekitar 0.00009 derajat)
        $tolerance = 10 / 111000; // 10 meter dalam derajat

        $result = DB::table('walkability_zones')
            ->join('user_searches as us', 'walkability_zones.search_id', '=', 'us.id')
            ->whereRaw("ST_DWithin(us.search_location, ST_SetSRID(ST_Point(?, ?), 4326), ?)", [
                $lng, $lat, $tolerance
            ])
            ->select(
                'walkability_zones.zone_polygon',
                'us.id as search_id',
                DB::raw('ST_AsGeoJSON(walkability_zones.zone_polygon) as zone_polygon_geojson')
            )
            ->first();

        if ($result) {
            $polygonData = json_decode($result->zone_polygon_geojson, true);
            
            return response()->json([
                'exists' => true,
                'search_id' => $result->search_id,
                'zone_polygon' => $polygonData, // Konsisten dengan nama field di frontend
            ]);
        } else {
            return response()->json([
                'exists' => false,
            ]);
        }
    }
}