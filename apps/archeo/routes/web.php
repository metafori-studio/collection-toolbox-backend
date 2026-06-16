<?php

use Illuminate\Http\Request;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;

Route::fallback(function (Request $request) {
    if (! $request->isMethod('GET') || $request->expectsJson()) {
        abort(404);
    }

    return response()->file(public_path('index.html'));
})->when(config('frontend.require_basic_auth'), function (RoutingRoute $route) {
    $route->middleware('auth.basic');
});
