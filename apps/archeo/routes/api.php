<?php

use Illuminate\Support\Facades\Route;
use Metafori\Archeo\Http\Controllers\Api\ActivityController;

Route::prefix('api/archeo')->middleware(['api'])->name('api.archeo.')->group(function () {
    Route::get('activities', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('activities/aggregations', [ActivityController::class, 'aggregations'])->name('activities.aggregations');
    Route::get('activities/map-points', [ActivityController::class, 'mapPoints'])->name('activities.map-points');
    Route::get('activities/{activity_number}', [ActivityController::class, 'show'])->name('activities.show');
});
