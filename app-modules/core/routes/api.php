<?php

use Illuminate\Support\Facades\Route;
use Metafori\Core\Http\Controllers\Api\AuthController;
use Metafori\Core\Http\Controllers\Api\PasswordController;

Route::prefix('api')->middleware(['api'])->name('api.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login');
    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('logout');
    Route::post('password/forgot', [PasswordController::class, 'forgot'])
        ->middleware('throttle:password.forgot')
        ->name('password.forgot');
    Route::post('password/reset', [PasswordController::class, 'reset'])
        ->middleware('throttle:password.reset')
        ->name('password.reset');
    Route::post('password/set', [PasswordController::class, 'set'])
        ->middleware('throttle:password.set')
        ->name('password.set');
});
