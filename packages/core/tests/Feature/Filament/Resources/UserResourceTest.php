<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Metafori\Core\Filament\Resources\UserResource\Pages\CreateUser;
use Metafori\Core\Models\User;
use Metafori\Core\Notifications\SetPassword;

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
    ]);

    $createdUser = User::where('email', 'john@example.com')->first();

    Notification::assertSentTo(
        [$createdUser],
        SetPassword::class
    );
});
