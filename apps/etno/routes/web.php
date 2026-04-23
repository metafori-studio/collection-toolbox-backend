<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::fallback(function (Request $request) {
    if (! $request->isMethod('GET') || $request->expectsJson()) {
        abort(404);
    }

    return response()->file(public_path('index.html'));
});
