<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $casts = [
        'boundary_polygon' => 'array', // jika simpan dalam bentuk GeoJSON string (opsional)
        'center_point' => 'array',
    ];

}
