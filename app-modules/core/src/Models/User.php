<?php

namespace Metafori\Core\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Metafori\Core\Database\Factories\UserFactory;
use Metafori\Core\Enums\Role;
use Metafori\Core\Notifications\SetPassword;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string|null $password
 * @property string|null $preferred_locale
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements FilamentUser, HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_locale',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_set_token',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Send the password set notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordSetNotification($token)
    {
        $this->notify(new SetPassword($token));
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole(Role::Admin);
    }

    public function preferredLocale(): string
    {
        return $this->preferred_locale ?? (string) config('app.fallback_locale');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdministrator();
    }
}
