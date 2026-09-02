<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Mail;
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
        'preferred_locale' => null,
    ]);

    $createdUser = User::where('email', 'john@example.com')->first();

    Notification::assertSentTo(
        [$createdUser],
        SetPassword::class
    );
});

it('sends the password set email in the user preferred locale', function () {
    Mail::fake();

    $user = User::factory()->create(['preferred_locale' => 'sk']);
    $sentLocale = null;

    SetPassword::toMailUsing(function () use (&$sentLocale): MailMessage {
        $sentLocale = app()->getLocale();

        return new MailMessage;
    });

    try {
        Notification::sendNow($user, new SetPassword('token'));
    } finally {
        SetPassword::toMailUsing(null);
    }

    expect($sentLocale)->toBe('sk');
});
