<?php

namespace Metafori\Core\Auth\Passwords;

use Closure;
use Illuminate\Auth\Passwords\PasswordBroker as ResetPasswordBroker;
use Illuminate\Contracts\Auth\PasswordBroker as BasePasswordBrokerContract;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Metafori\Core\Models\User;

class PasswordBroker implements BasePasswordBrokerContract
{
    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    public const SET_LINK_SENT = 'passwords.sent_set';

    /**
     * Constant representing a successfully set password.
     *
     * @var string
     */
    public const PASSWORD_SET = 'passwords.set';

    /**
     * Create a new password broker instance.
     */
    public function __construct(
        protected ResetPasswordBroker $broker,
        protected Hasher $hasher,
        protected Timebox $timebox = new Timebox,
        protected int $timeboxDuration = 200000
    ) {}

    public function sendSetLink(User $user): string
    {
        $token = $this->createSetToken($user);

        $user->sendPasswordSetNotification($token);

        return static::SET_LINK_SENT;
    }

    /**
     * Set the password for the given token.
     */
    public function set(#[\SensitiveParameter] array $credentials, Closure $callback): string
    {
        return $this->timebox->call(function ($timebox) use ($credentials, $callback) {
            $user = $this->validateSet($credentials);

            if (! $user instanceof User) {
                return $user;
            }

            $password = $credentials['password'];

            $callback($user, $password);

            $user->forceFill([
                'password_set_token' => null,
            ])->save();

            $timebox->returnEarly();

            return static::PASSWORD_SET;
        }, $this->timeboxDuration);
    }

    /**
     * Validate a password set for the given credentials.
     */
    protected function validateSet(#[\SensitiveParameter] array $credentials): User|string
    {
        if (is_null($user = $this->broker->getUser($credentials))) {
            return static::INVALID_USER;
        }

        if (
            empty($user->password_set_token) ||
            ! $this->hasher->check($credentials['token'], $user->password_set_token)
        ) {
            return static::INVALID_TOKEN;
        }

        return $user;
    }

    public function sendResetLink(array $credentials, ?Closure $callback = null): string
    {
        return $this->broker->sendResetLink($credentials, $callback);
    }

    public function reset(#[\SensitiveParameter] array $credentials, Closure $callback): string
    {
        return $this->broker->reset($credentials, $callback);
    }

    /**
     * Create a new password set token for the given user.
     */
    public function createSetToken(User $user): string
    {
        return tap(Str::random(60), function ($token) use ($user) {
            $user->forceFill([
                'password_set_token' => $this->hashToken($token),
            ])->save();
        });
    }

    /**
     * Hash the given token.
     */
    protected function hashToken(string $token): string
    {
        return $this->hasher->make($token);
    }

    /**
     * Create a new password reset token for the given user.
     */
    public function createResetToken(User $user): string
    {
        return $this->broker->createToken($user);
    }
}
