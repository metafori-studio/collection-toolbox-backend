<?php

use Illuminate\Support\Facades\Route;
use Metafori\Etno\Http\Controllers\Api\ItemController;
use Metafori\Etno\Http\Controllers\Api\TranslationController;

Route::prefix('api/etno')->middleware(['api'])->name('api.etno.')->group(function () {
    Route::get('items/aggregations', [ItemController::class, 'aggregations'])
        ->name('items.aggregations');
    Route::get('items/search', [ItemController::class, 'search'])
        ->name('items.search');
    Route::get('items/map-points', [ItemController::class, 'mapPoints'])
        ->name('items.map-points');
    Route::apiResource('items', ItemController::class)->only([
        'index',
        'show',
    ]);
    Route::get('translations', [TranslationController::class, 'index'])
        ->name('translations.index');
});
