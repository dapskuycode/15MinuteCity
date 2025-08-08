<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    /**
     * Mencari ID kecamatan bedasarkan nama
     */
    public function showByName($name)
    {
        // 1. Cari hanya ID dari district berdasarkan nama (case-insensitive)
        //    Menggunakan ->value('id') lebih efisien karena hanya mengambil satu nilai.
        $districtId = DB::table('districts')
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->value('id');

        // 2. Jika ID tidak ditemukan, kembalikan error 404
        if (!$districtId) {
            return response()->json(['message' => 'District not found'], 404);
        }

        // 3. Jika ID ditemukan, panggil fungsi show() yang asli dengan ID tersebut.
        //    $this->show() memanggil fungsi lain di dalam controller yang sama.
        return $this->show($districtId);
    }

    /**
     * Tampilkan detail district beserta kelurahannya (dengan polygon)
     */
    public function show($id)
    {
        // Ambil district dengan GeoJSON polygon
        $district = DB::table('districts')
            ->select(
                'id',
                'name',
                'persentase_penduduk',
                'kepadatan_penduduk_per_km2',
                DB::raw('ST_AsGeoJSON(polygon) as polygon_geojson')
            )
            ->where('id', $id)
            ->first();

        $district->polygon = json_decode($district->polygon_geojson);

        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }

        // Ambil kelurahan terkait dengan GeoJSON polygon
        $kelurahans = DB::table('kelurahans')
            ->select(
                'id',
                'name',
                'district_id',
                DB::raw('ST_AsGeoJSON(polygon) as polygon_geojson')
            )
            ->where('district_id', $id)
            ->get();

        $kelurahans->transform(function ($kelurahan) {
            $kelurahan->polygon = json_decode($kelurahan->polygon_geojson);
            return $kelurahan;
        });
        
        $district->kelurahans = $kelurahans;

        return response()->json($district);
    }

    /**
     * Tampilkan detail district tanpa polygon (ringkas)
     */
    public function detail($id)
    {
        $district = DB::table('districts')
            ->select(
                'id',
                'name',
                'persentase_penduduk',
                'kepadatan_penduduk_per_km2'
            )
            ->where('id', $id)
            ->first();

        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }

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

    /**
     * Cari informasi Kecamatan dan Kelurahan berdasarkan koordinat lat/lng.
     */
    public function getRegionInfoByCoords(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        // Buat WKT (Well-Known Text) untuk titik dari koordinat
        $pointWkt = "POINT({$validated['lng']} {$validated['lat']})";

        // Cari kecamatan yang poligonnya berisi titik tersebut
        $district = DB::table('districts')
            ->select('id', 'name', 'persentase_penduduk', 'kepadatan_penduduk_per_km2')
            ->whereRaw('ST_Contains(polygon, ST_GeomFromText(?, 4326))', [$pointWkt])
            ->first();

        if (!$district) {
            return response()->json(['message' => 'Coordinates outside of any district'], 404);
        }

        // Cari kelurahan yang poligonnya berisi titik tersebut
        $kelurahan = DB::table('kelurahans')
            ->select('name')
            ->where('district_id', $district->id)
            ->whereRaw('ST_Contains(polygon, ST_GeomFromText(?, 4326))', [$pointWkt])
            ->first();

        return response()->json([
            'kecamatan' => $district->name,
            'kelurahan' => $kelurahan ? $kelurahan->name : 'N/A', // Handle jika tidak ada kelurahan yg cocok
            'population_density' => (int) $district->kepadatan_penduduk_per_km2,
            'population_percentage' => (float) $district->persentase_penduduk,
        ]);
    }
}
