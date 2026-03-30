<?php

use Illuminate\Support\Facades\Route;

Route::get('/{path?}', function (?string $path = null) {
    if ($path && file_exists(public_path($path))) {
        return response()->file(public_path($path));
    }

    return response()->file(public_path('index.html'));
})->where('path', '.*');
