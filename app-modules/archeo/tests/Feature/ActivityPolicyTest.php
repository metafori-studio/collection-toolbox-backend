<?php

use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Policies\ActivityPolicy;
use Metafori\Core\Enums\Role;
use Metafori\Core\Models\User;

test('admin is authorized via before', function () {
    $policy = new ActivityPolicy;

    $admin = Mockery::mock(User::class);
    $admin->shouldReceive('hasRole')->with(Role::Admin)->andReturn(true);

    expect($policy->before($admin, 'viewAny'))->toBeTrue();
    expect($policy->before($admin, 'create'))->toBeTrue();
});

test('non-admin is not authorized via before', function () {
    $policy = new ActivityPolicy;

    $user = Mockery::mock(User::class);
    $user->shouldReceive('hasRole')->with(Role::Admin)->andReturn(false);

    expect($policy->before($user, 'viewAny'))->toBeNull();
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

test('other methods deny non-admin access', function () {
    $policy = new ActivityPolicy;
    $user = Mockery::mock(User::class);

    expect($policy->viewAny($user))->toBeFalse();
    expect($policy->create($user))->toBeFalse();
    expect($policy->update($user, Mockery::mock(Activity::class)))->toBeFalse();
    expect($policy->delete($user, Mockery::mock(Activity::class)))->toBeFalse();
    expect($policy->deleteAny($user))->toBeFalse();
    expect($policy->restore($user, Mockery::mock(Activity::class)))->toBeFalse();
    expect($policy->forceDelete($user, Mockery::mock(Activity::class)))->toBeFalse();
    expect($policy->import($user))->toBeFalse();
});
