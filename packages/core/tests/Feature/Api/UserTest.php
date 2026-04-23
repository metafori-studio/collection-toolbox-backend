<?php

use Metafori\Core\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

test('authenticated user can retrieve their profile', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    actingAs($user)
        ->getJson(route('api.me'))
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', 'test@example.com');
});

test('unauthenticated user cannot retrieve profile', function () {
    getJson(route('api.me'))
        ->assertUnauthorized();
});
