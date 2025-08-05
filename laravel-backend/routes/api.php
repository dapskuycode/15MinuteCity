<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceCategoryController;
use App\Http\Controllers\Api\PublicServiceController;
use App\Http\Controllers\Api\UserSearchController;
use App\Http\Controllers\Api\WalkabilityZoneController;
use App\Http\Controllers\Api\ServiceImageController;
use App\Http\Controllers\Api\ServiceReviewController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\KelurahanController;

// District
Route::get('/districts/{id}', [DistrictController::class, 'show']); // detail + polygon
Route::get('/districts/{id}/detail', [DistrictController::class, 'detail']); // detail tanpa polygon

// Kelurahan
Route::get('/kelurahans/{id}', [KelurahanController::class, 'show']);


Route::get('/test-api', function () {
    return response()->json(['message' => 'API Works!']);
});

Route::apiResource('categories', ServiceCategoryController::class);
Route::apiResource('public-services', PublicServiceController::class);
Route::apiResource('user-searches', UserSearchController::class);
Route::apiResource('walkability-zones', WalkabilityZoneController::class);
Route::apiResource('service-images', ServiceImageController::class);
Route::apiResource('service-reviews', ServiceReviewController::class);
Route::apiResource('districts', DistrictController::class);

//untuk menyimpan titik koordinat dan poligon
Route::post('/save-zone', [WalkabilityZoneController::class, 'store']);
Route::post('/save-point', [UserSearchController::class, 'store']);
Route::post('/check-user-search', [\App\Http\Controllers\Api\WalkabilityZoneController::class, 'check']);

//menampilkan fasilitas publik
Route::get('/public-services-in-zone/{search_id}', [PublicServiceController::class, 'getInZone']);
