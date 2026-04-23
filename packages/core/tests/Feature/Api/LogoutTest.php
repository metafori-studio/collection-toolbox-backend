<?php

use Metafori\Core\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\postJson;

test('authenticated user can logout', function () {
    $user = User::factory()->create();

    actingAs($user, 'web');

    postJson(route('api.logout'))
        ->assertNoContent();

    assertGuest('web');
});

test('guest cannot logout', function () {
    postJson(route('api.logout'))
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});
