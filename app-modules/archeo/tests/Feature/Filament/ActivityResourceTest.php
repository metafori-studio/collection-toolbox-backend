<?php

namespace Metafori\Archeo\Tests\Feature\Filament;

use Metafori\Archeo\Filament\Resources\ActivityResource;
use Metafori\Archeo\Filament\Resources\ActivityResource\Pages\CreateActivity;
use Metafori\Archeo\Filament\Resources\ActivityResource\Pages\EditActivity;
use Metafori\Archeo\Filament\Resources\ActivityResource\Pages\ListActivities;
use Metafori\Archeo\Filament\Resources\ActivityResource\Pages\ViewActivity;
use Metafori\Archeo\Filament\Resources\ActivityResource\RelationManagers\AssignmentsRelationManager;
use Metafori\Archeo\Filament\Resources\ActivityResource\RelationManagers\GalleriesRelationManager;
use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;

use function Pest\Livewire\livewire;

// ── getGloballySearchableAttributes ────────────────────────────────────────

it('returns the expected globally searchable attributes', function () {
    $attributes = ActivityResource::getGloballySearchableAttributes();

    expect($attributes)->toContain('activity_number')
        ->toContain('cvs_number')
        ->toContain('registration_year')
        ->toContain('activity_type')
        ->toContain('cadastral_area')
        ->toContain('municipality')
        ->toContain('position')
        ->toContain('district')
        ->toContain('research_leader')
        ->toContain('institution')
        ->toContain('action_number')
        ->toContain('site_type_original')
        ->toContain('size_category')
        ->toContain('import_id');
});

it('returns exactly 14 globally searchable attributes', function () {
    expect(ActivityResource::getGloballySearchableAttributes())->toHaveCount(14);
});

// ── getRelations ────────────────────────────────────────────────────────────

it('registers GalleriesRelationManager', function () {
    expect(ActivityResource::getRelations())->toContain(GalleriesRelationManager::class);
});

it('registers AssignmentsRelationManager', function () {
    expect(ActivityResource::getRelations())->toContain(AssignmentsRelationManager::class);
});

it('registers exactly two relation managers', function () {
    expect(ActivityResource::getRelations())->toHaveCount(2);
});

// ── getPages ────────────────────────────────────────────────────────────────

it('registers the index page', function () {
    $pages = ActivityResource::getPages();

    expect($pages)->toHaveKey('index')
        ->and($pages['index']->getPage())->toBe(ListActivities::class);
});

it('registers the create page', function () {
    $pages = ActivityResource::getPages();

    expect($pages)->toHaveKey('create')
        ->and($pages['create']->getPage())->toBe(CreateActivity::class);
});

it('registers the view page', function () {
    $pages = ActivityResource::getPages();

    expect($pages)->toHaveKey('view')
        ->and($pages['view']->getPage())->toBe(ViewActivity::class);
});

it('registers the edit page', function () {
    $pages = ActivityResource::getPages();

    expect($pages)->toHaveKey('edit')
        ->and($pages['edit']->getPage())->toBe(EditActivity::class);
});

// ── getEloquentQuery ────────────────────────────────────────────────────────

it('returns no activities for a non-admin user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Activity::factory()->count(3)->create();

    $query = ActivityResource::getEloquentQuery();

    expect($query->count())->toBe(0);
});

it('returns all activities for an admin user', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    Activity::factory()->count(3)->create();

    $query = ActivityResource::getEloquentQuery();

    expect($query->count())->toBe(3);
});

it('returns no activities when no user is authenticated', function () {
    Activity::factory()->count(2)->create();

    $query = ActivityResource::getEloquentQuery();

    expect($query->count())->toBe(0);
});

it('returns only activities for admin even when there are many records', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Activity::factory()->count(10)->create();

    expect(ActivityResource::getEloquentQuery()->count())->toBe(10);
});

// ── ListActivities page ─────────────────────────────────────────────────────

it('renders the list activities page for an admin', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    livewire(ListActivities::class)
        ->assertSuccessful();
});

it('shows activities in the table for an admin', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create();

    livewire(ListActivities::class)
        ->assertCanSeeTableRecords([$activity]);
});

it('shows no activities in the table for a non-admin', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Activity::factory()->count(3)->create();

    livewire(ListActivities::class)
        ->assertCountTableRecords(0);
});

// ── EditActivity page ────────────────────────────────────────────────────────

it('renders the edit activity page for an admin', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create();

    livewire(EditActivity::class, ['record' => $activity->activity_number])
        ->assertSuccessful();
});

it('saves activity form data correctly', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create();

    livewire(EditActivity::class, ['record' => $activity->activity_number])
        ->fillForm([
            'municipality' => 'Updated Municipality',
            'district' => 'Updated District',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $activity->refresh();
    expect($activity->municipality)->toBe('Updated Municipality')
        ->and($activity->district)->toBe('Updated District');
});

it('validates that activity_number only accepts digits', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create();

    livewire(EditActivity::class, ['record' => $activity->activity_number])
        ->fillForm([
            'activity_number' => 'ABC123',
        ])
        ->call('save')
        ->assertHasFormErrors(['activity_number']);
});

it('validates that activity_number is required', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create();

    livewire(EditActivity::class, ['record' => $activity->activity_number])
        ->fillForm([
            'activity_number' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['activity_number']);
});

// ── CreateActivity page ──────────────────────────────────────────────────────

it('renders the create activity page for an admin', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    livewire(CreateActivity::class)
        ->assertSuccessful();
});

it('creates a new activity with valid data', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    livewire(CreateActivity::class)
        ->fillForm([
            'activity_number' => '999001',
            'activity_type' => 'Excavation',
            'cvs_number' => 1234,
            'activity_year_start' => 2020,
            'activity_year_end' => 2021,
            'research_leader' => 'Dr. Test',
            'size_category' => 'medium',
            'author_ns' => ['Test Author'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('archeo_activities', [
        'activity_number' => '999001',
        'activity_type' => 'Excavation',
    ]);
});

it('validates required fields on create', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    livewire(CreateActivity::class)
        ->fillForm([
            'activity_number' => '',
            'activity_type' => '',
            'cvs_number' => '',
        ])
        ->call('create')
        ->assertHasFormErrors(['activity_number', 'activity_type', 'cvs_number']);
});

// ── ViewActivity page ────────────────────────────────────────────────────────

it('renders the view activity page for an admin', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $activity = Activity::factory()->create();

    livewire(ViewActivity::class, ['record' => $activity->activity_number])
        ->assertSuccessful();
});