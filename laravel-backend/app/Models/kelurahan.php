<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    protected $table = 'kelurahans';
    protected $fillable = [
        'name',
        'district_id',
        'polygon'
    ];

    protected $casts = [
        'polygon' => 'json',
    ];

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
