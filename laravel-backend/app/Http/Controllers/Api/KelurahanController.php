<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KelurahanController extends Controller
{
    /**
     * Mencari ID kelurahan bedasarkan nama
     */
    public function showByName($name)
    {
        $kelurahanId = DB::table('kelurahans')
            // Menggunakan REPLACE untuk membuang 'Kelurahan ' dari nama sebelum membandingkan.
            ->whereRaw("LOWER(REPLACE(name, 'Kelurahan ', '')) = ?", [strtolower($name)])
            ->value('id');

        if (!$kelurahanId) {
            return response()->json(['message' => 'Kelurahan not found'], 404);
        }

        return $this->show($kelurahanId);
    }
    /**
     * Tampilkan detail kelurahan dengan polygon
     */
    public function show($id)
    {
        $kelurahan = DB::table('kelurahans')
            ->select(
                'id',
                'name',
                'district_id',
                DB::raw('ST_AsGeoJSON(polygon) as polygon_geojson')
            )
            ->where('id', $id)
            ->first();

        if (!$kelurahan) {
            return response()->json(['message' => 'Kelurahan not found'], 404);
        }

        $kelurahan->polygon = json_decode($kelurahan->polygon_geojson);

        return response()->json($kelurahan);
    }

    public function showInfo($id)
    {
        $kelurahan = DB::table('kelurahans')
            ->select(
                'id',
                'name',
                'district_id',
            )
            ->where('id', $id)
            ->first();

        if (!$kelurahan) {
            return response()->json(['message' => 'Kelurahan not found'], 404);
        }

        return response()->json($kelurahan);
    }
}