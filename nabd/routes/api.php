<?php

use App\Http\Controllers\Api\AnalysisApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PatientApiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; 

use App\Http\Controllers\Api\BannerApiController;

Route::get('/banners', [BannerApiController::class, 'index']);
Route::post('/register',[AuthApiController::class,'register']);
Route::post('/login',[AuthApiController::class,'login']);

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return response()->json([
        'user' => $request->user()
    ]);
});

// Route::get('/banners', function () {
//     return \App\Models\Banner::where('is_active', true)
//         ->orderBy('sort_order')
//         ->get()
//         ->map(fn ($b) => [
//             'id' => $b->id,
//             'title' => $b->title,
//             'description' => $b->description,
//             'imageUrl' => $b->image_url, // 👈 مهم
//             'buttonText' => $b->button_text,
//             'buttonUrl' => $b->button_url,
//         ]);
// });

Route::middleware('auth:sanctum')->group(function(){

        Route::get('/analyses', [AnalysisApiController::class, 'index']); // ✅ أضف هذا


    Route::get('/patients',[PatientApiController::class,'index']);
    Route::post('/patients',[PatientApiController::class,'store']);

    Route::post('/analyses',[AnalysisApiController::class,'store']);

    Route::post('/analyses/{id}/symptoms',[AnalysisApiController::class,'addSymptoms']);
    Route::post('/analyses/{id}/tests',[AnalysisApiController::class,'addTests']);
    Route::post('/analyses/{id}/image',[AnalysisApiController::class,'uploadImage']);
    Route::post('/analyses/{id}/predict',[AnalysisApiController::class,'predict']);

});