<?php
// app/Models/WalkabilityZone.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalkabilityZone extends Model
{
    protected $fillable = [
        'search_id',
        'zone_polygon',
        'zone_type',
        'travel_time',
    ];

    public $timestamps = false; // jika kamu tidak pakai created_at
}
