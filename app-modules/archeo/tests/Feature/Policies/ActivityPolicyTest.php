<?php

use Metafori\Archeo\Enums\Permission;
use Metafori\Archeo\Models\Activity;
use Metafori\Core\Database\Seeders\PermissionSeeder;
use Metafori\Core\Database\Seeders\RoleSeeder;
use Metafori\Core\Models\User;

beforeEach(function () {
    $this->seed([
        RoleSeeder::class,
        PermissionSeeder::class,
    ]);
});

it('allows a user with activity.view-any-document permission to view documents on any activity without assignment', function () {
    $user = User::factory()->create();
    $activity = Activity::factory()->create();

    expect($user->can('viewDocument', $activity))->toBeFalse();

    $user->givePermissionTo(Permission::ActivityViewAnyDocument);

    expect($user->can('viewDocument', $activity))->toBeTrue();
});

it('allows an admin to view documents on any activity', function () {
    $admin = User::factory()->admin()->create();
    $activity = Activity::factory()->create();

    expect($admin->can('viewDocument', $activity))->toBeTrue();
});

it('allows an assigned user with non-expired access to view documents only on assigned activities', function () {
    $user = User::factory()->create();
    $assignedActivity = Activity::factory()->create();
    $unassignedActivity = Activity::factory()->create();

    $assignedActivity->assignments()->create([
        'user_id' => $user->id,
        'expires_at' => now()->addDay(),
    ]);

    expect($user->can('viewDocument', $assignedActivity))->toBeTrue()
        ->and($user->can('viewDocument', $unassignedActivity))->toBeFalse();
});

it('denies document access for expired assignments', function () {
    $user = User::factory()->create();
    $activity = Activity::factory()->create();

    $activity->assignments()->create([
        'user_id' => $user->id,
        'expires_at' => now()->subMinute(),
    ]);

    expect($user->can('viewDocument', $activity))->toBeFalse();
});
