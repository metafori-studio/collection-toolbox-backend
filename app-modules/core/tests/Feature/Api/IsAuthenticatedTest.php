<?php

use Illuminate\Support\Facades\Route;
use Metafori\Core\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function () {
    Route::get('/api/test-auth', fn () => 'ok')->middleware('api');
});

it('does not add X-Is-Authenticated header for guests', function () {
    $response = get('/api/test-auth');

    $response->assertOk();
    $response->assertHeaderMissing('X-Is-Authenticated');
});

it('adds X-Is-Authenticated header for authenticated users', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->get('/api/test-auth');

    $response->assertOk();
    $response->assertHeader('X-Is-Authenticated', 'true');
});

it('does not add X-Is-Authenticated header after logout', function () {
    $user = User::factory()->create();

    $response = actingAs($user)->post('/api/logout');

    $response->assertNoContent();
    $response->assertHeaderMissing('X-Is-Authenticated');
});
