<?php

use Illuminate\Support\Facades\Route;
use Metafori\Archeo\Http\Controllers\Api\ActivityController;

Route::prefix('api/archeo')->middleware(['api'])->group(function () {
    Route::get('activities', [ActivityController::class, 'index']);
    Route::get('activities/aggregations', [ActivityController::class, 'aggregations']);
    Route::get('activities/map-points', [ActivityController::class, 'mapPoints']);
    Route::get('activities/{activity_number}', [ActivityController::class, 'show']);
});
