<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DistrictController extends Controller
{
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
                DB::raw('ST_AsGeoJSON(polygon)::json as polygon')
            )
            ->where('id', $id)
            ->first();

        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }

        // Ambil kelurahan terkait dengan GeoJSON polygon
        $kelurahans = DB::table('kelurahans')
            ->select(
                'id',
                'name',
                'district_id',
                DB::raw('ST_AsGeoJSON(polygon)::json as polygon')
            )
            ->where('district_id', $id)
            ->get();

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
}
