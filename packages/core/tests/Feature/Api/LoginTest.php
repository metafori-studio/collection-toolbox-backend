<?php

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Metafori\Core\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

test('successful login returns no content response', function () {
    $user = User::factory()->create();

    postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertNoContent();
});

test('no password returns unprocessable entity response', function () {
    $user = User::factory()->create();

    postJson(route('api.login'), [
        'email' => $user->email,
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('empty password returns unprocessable entity response', function () {
    $user = User::factory()->create();

    postJson(route('api.login'), [
        'email' => $user->email,
        'password' => '',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('wrong password returns unprocessable entity response', function () {
    $user = User::factory()->create();

    postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('login requests are throttled after 5 attempts', function () {
    $url = route('api.login');

    for ($i = 0; $i < 5; $i++) {
        postJson($url, [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    postJson($url, [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
});
