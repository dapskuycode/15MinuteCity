<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $table = 'districts';
    protected $fillable = [
        'name',
        'persentase_penduduk',
        'kepadatan_penduduk_per_km2',
        'polygon'
    ];

    protected $casts = [
        'polygon' => 'json', // kalau polygon disimpan dalam format GeoJSON string
    ];

    public function kelurahans()
    {
        return $this->hasMany(Kelurahan::class, 'district_id');
    }
}
