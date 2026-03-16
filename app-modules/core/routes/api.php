<?php

use Illuminate\Support\Facades\Route;
use Metafori\Core\Http\Controllers\Api\AuthController;

Route::prefix('api')->middleware(['api'])->name('api.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login');
    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('logout');
});
