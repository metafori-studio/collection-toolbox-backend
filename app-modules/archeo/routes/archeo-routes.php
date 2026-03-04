<?php

use Illuminate\Support\Facades\Route;
use Metafori\Archeo\Http\Controllers\ActivityController;

Route::prefix('api/archeo')->middleware('api')->group(function () {
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::get('/activities/map-points', [ActivityController::class, 'mapPoints']);
    Route::get('/activities/{activity:activity_number}', [ActivityController::class, 'show']);
});
