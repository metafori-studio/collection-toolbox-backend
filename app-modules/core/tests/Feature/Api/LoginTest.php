<?php

use Metafori\Core\Models\User;

use function Pest\Laravel\postJson;

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
    ])->assertUnprocessable();
});

test('empty password returns unprocessable entity response', function () {
    $user = User::factory()->create();

    postJson(route('api.login'), [
        'email' => $user->email,
        'password' => '',
    ])->assertUnprocessable();
});

test('wrong password returns unprocessable entity response', function () {
    $user = User::factory()->create();

    postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertUnprocessable();
});

test('login requests are throttled after 5 attempts', function () {
    $url = route('api.login');

    for ($i = 0; $i < 5; $i++) {
        postJson($url, [
            'email' => 'test@example.com',
            'password' => 'password',
        ])->assertUnprocessable();
    }

    postJson($url, [
        'email' => 'test@example.com',
        'password' => 'password',
    ])->assertTooManyRequests();
});

test('stateless login requests are forbidden', function () {
    $this->withoutHeader('referer');

    $user = User::factory()->create();

    postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertForbidden();
});
