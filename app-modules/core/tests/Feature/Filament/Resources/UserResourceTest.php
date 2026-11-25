<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Metafori\Core\Database\Seeders\PermissionSeeder;
use Metafori\Core\Filament\Resources\UserResource\Pages\CreateUser;
use Metafori\Core\Filament\Resources\UserResource\Pages\EditUser;
use Metafori\Core\Models\Permission;
use Metafori\Core\Models\User;
use Metafori\Core\Notifications\SetPassword;
use Metafori\Core\Tests\Fixtures\TestPermission;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

it('can create a user and sends a password set link', function () {
    Notification::fake();

    $this->actingAs(User::factory()->admin()->create());

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'preferred_locale' => null,
    ]);

    $createdUser = User::where('email', 'john@example.com')->first();

    Notification::assertSentTo(
        [$createdUser],
        SetPassword::class
    );
});

it('sends the password set email in the user preferred locale', function () {
    $user = User::factory()->create(['preferred_locale' => 'sk']);
    App::setLocale($user->preferredLocale());

    $mailMessage = (new SetPassword('token'))->toMail($user);

    expect($mailMessage)
        ->subject->toBe('Účet bol vytvorený')
        ->introLines->toContain('Váš účet bol vytvorený. Ak chcete pokračovať, nastavte si heslo.')
        ->actionText->toBe('Nastaviť heslo')
        ->outroLines->toContain('Ak sa domnievate, že tento e-mail bol odoslaný omylom, nie je potrebné vykonávať žiadne ďalšie kroky.');
});

it('can assign permissions to a user when a permission enum is configured', function () {
    Config::set('permission.enums.permission', TestPermission::class);
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();
    $permission = Permission::findByName(TestPermission::Example->value);

    $this->actingAs($admin);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertFormFieldVisible('permissions')
        ->fillForm([
            'permissions' => [$permission->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->checkPermissionTo(TestPermission::Example))->toBeTrue();
});

it('hides the permissions field when no permission enum is configured', function () {
    Config::set('permission.enums.permission', null);

    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertFormFieldHidden('permissions');
});
