<?php

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Metafori\Core\Models\User;
use Metafori\Core\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\postJson;

test('user can set password with valid token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::broker()->createSetToken($user);

    postJson(route('api.password.set'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'New-p4$$word',
    ])->assertOk();

    $this->assertTrue(Hash::check('New-p4$$word', $user->fresh()->password));
});

test('user cannot set password with invalid token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    postJson(route('api.password.set'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'New-p4$$word',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
});

test('user can request password reset link for valid email', function () {
    Notification::fake();

    $user = User::factory()->create();

    postJson(route('api.password.forgot'), [
        'email' => $user->email,
    ])->assertOk();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('user cannot request password reset link for invalid email', function () {
    postJson(route('api.password.forgot'), [
        'email' => 'doesnotexist@example.com',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('user can reset password with valid token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::broker()->createResetToken($user);

    postJson(route('api.password.reset'), [
        'token' => $token,
        'email' => $user->email,
        'password' => 'New-p4$$word',
    ])->assertOk();

    $this->assertTrue(Hash::check('New-p4$$word', $user->fresh()->password));
});

test('user cannot reset password with invalid token', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password'),
    ]);

    postJson(route('api.password.reset'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'New-p4$$word',
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
});
