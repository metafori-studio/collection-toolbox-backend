<?php

use Illuminate\Support\Facades\Gate;
use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Policies\ActivityPolicy;
use Metafori\Core\Models\User;

test('admin is authorized via gate interceptor', function () {
    $admin = Mockery::mock(User::class);
    $admin->shouldReceive('isAdministrator')->andReturn(true);

    Gate::before(function ($user, $ability) {
        if ($user->isAdministrator()) {
            return true;
        }
    });

    expect(Gate::forUser($admin)->allows('viewAny'))->toBeTrue();
    expect(Gate::forUser($admin)->allows('create'))->toBeTrue();
});

test('non-admin can view document if assigned', function () {
    $policy = new ActivityPolicy;

    $user = Mockery::mock(User::class);
    $activity = Mockery::mock(Activity::class);

    $activity->shouldReceive('isAssignedTo')->with($user)->andReturn(true);

    expect($policy->viewDocument($user, $activity))->toBeTrue();
});

test('non-admin cannot view document if not assigned', function () {
    $policy = new ActivityPolicy;

    $user = Mockery::mock(User::class);
    $activity = Mockery::mock(Activity::class);

    $activity->shouldReceive('isAssignedTo')->with($user)->andReturn(false);

    expect($policy->viewDocument($user, $activity))->toBeFalse();
});
