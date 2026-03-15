<?php

use Illuminate\Support\Facades\Route;
use Metafori\Core\Http\Controllers\Api\AuthController;
use Metafori\Core\Http\Controllers\Api\PasswordResetController;

Route::prefix('api')->middleware(['api'])->name('api.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:login')
        ->name('login');
    Route::post('logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('logout');
    Route::post('password/forgot', [PasswordResetController::class, 'forgotPassword'])
        ->name('password.forgot');
    Route::post('password/reset', [PasswordResetController::class, 'resetPassword'])
        ->name('password.reset');
});
