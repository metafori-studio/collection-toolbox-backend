<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
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
    $user = User::factory()->create(['preferred_locale' => 'sk']);
    App::setLocale($user->preferredLocale());

    $mailMessage = (new SetPassword('token'))->toMail($user);

    expect($mailMessage)
        ->subject->toBe('Účet bol vytvorený')
        ->introLines->toContain('Váš účet bol vytvorený. Ak chcete pokračovať, nastavte si heslo.')
        ->actionText->toBe('Nastaviť heslo')
        ->outroLines->toContain('Ak sa domnievate, že tento e-mail bol odoslaný omylom, nie je potrebné vykonávať žiadne ďalšie kroky.');
});
