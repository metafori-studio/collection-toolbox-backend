<?php

use Metafori\Core\Models\User;
use Metafori\Core\Support\Frontend;

it('generates reset password url with query parameters', function () {
    config()->set('frontend.url', 'http://localhost:3000');
    config()->set('frontend.routes.reset_password', '/{locale}/reset-password');

    $frontend = new Frontend;
    $user = User::factory()->make(['email' => 'test@example.com', 'preferred_locale' => 'sk']);

    $url = $frontend->resetPasswordUrl($user, 'secret-token');

    expect($url)->toBe('http://localhost:3000/sk/reset-password?token=secret-token&email=test%40example.com');
});

it('generates reset password url with route parameters', function () {
    config()->set('frontend.url', 'http://localhost:3000');
    config()->set('frontend.routes.reset_password', '/{locale}/reset-password/{email}/{token}');

    $frontend = new Frontend;
    $user = User::factory()->make(['email' => 'test@example.com', 'preferred_locale' => 'sk']);

    $url = $frontend->resetPasswordUrl($user, 'secret-token');

    expect($url)->toBe('http://localhost:3000/sk/reset-password/test%40example.com/secret-token');
});

it('generates set password url with query parameters', function () {
    config()->set('frontend.url', 'http://localhost:3000');
    config()->set('frontend.routes.set_password', '/{locale}/set-password');

    $frontend = new Frontend;
    $user = User::factory()->make(['email' => 'test@example.com', 'preferred_locale' => 'sk']);

    $url = $frontend->setPasswordUrl($user, 'secret-token');

    expect($url)->toBe('http://localhost:3000/sk/set-password?token=secret-token&email=test%40example.com');
});

it('generates set password url with route parameters', function () {
    config()->set('frontend.url', 'http://localhost:3000');
    config()->set('frontend.routes.set_password', '/{locale}/setup-account/{email}/{token}');

    $frontend = new Frontend;
    $user = User::factory()->make(['email' => 'test@example.com', 'preferred_locale' => 'sk']);

    $url = $frontend->setPasswordUrl($user, 'secret-token');

    expect($url)->toBe('http://localhost:3000/sk/setup-account/test%40example.com/secret-token');
});

it('throws exception when placeholder is missing', function () {
    config()->set('frontend.url', 'http://localhost:3000');
    config()->set('frontend.routes.reset_password', '/reset-password/{email}/{token}/{missing}');

    $frontend = new Frontend;
    $user = User::factory()->make(['email' => 'test@example.com']);

    expect(fn () => $frontend->resetPasswordUrl($user, 'secret-token'))
        ->toThrow(InvalidArgumentException::class, 'Missing frontend route parameter [missing].');
});

it('uses the fallback locale when the user has no preferred locale', function () {
    config()->set('app.fallback_locale', 'en');
    config()->set('frontend.url', 'http://localhost:3000');
    config()->set('frontend.routes.set_password', '/{locale}/setup-account');

    $user = User::factory()->make(['email' => 'test@example.com', 'preferred_locale' => null]);

    expect((new Frontend)->setPasswordUrl($user, 'secret-token'))
        ->toBe('http://localhost:3000/en/setup-account?token=secret-token&email=test%40example.com');
});
