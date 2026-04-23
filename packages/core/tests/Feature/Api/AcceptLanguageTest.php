<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\getJson;

beforeEach(function () {
    config([
        'app.locale' => 'en',
        'app.locales' => ['en', 'sk'],
        'app.fallback_locale' => 'sk',
    ]);

    Route::get('/api/test-locale', app()->getLocale(...))
        ->middleware('api');
});

test('api honors accept language header when processing requests', function () {
    getJson('/api/test-locale', ['Accept-Language' => 'sk'])
        ->assertSee('sk');
});

test('api falls back to supported language in accept language header', function () {
    getJson('/api/test-locale', ['Accept-Language' => 'cs, sk'])
        ->assertSee('sk');
});

test('api falls back to default locale when requested languages are unsupported', function () {
    getJson('/api/test-locale', ['Accept-Language' => 'cs, pl'])
        ->assertSee('en');
});
