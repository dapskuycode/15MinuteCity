<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KelurahanController extends Controller
{
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
                DB::raw('ST_AsGeoJSON(polygon)::json as polygon')
            )
            ->where('id', $id)
            ->first();

        if (!$kelurahan) {
            return response()->json(['message' => 'Kelurahan not found'], 404);
        }

        return response()->json($kelurahan);
    }
}
