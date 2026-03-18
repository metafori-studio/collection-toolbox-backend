<?php

namespace Metafori\Core\Auth\Passwords;

use Closure;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker as ResetPasswordBroker;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Timebox;
use Metafori\Core\Auth\Events\PasswordSetLinkSent;
use Metafori\Core\Auth\Passwords\Contracts\CanSetPassword as CanSetPasswordContract;
use Metafori\Core\Auth\Passwords\Contracts\PasswordBroker as PasswordBrokerContract;

class PasswordBroker implements PasswordBrokerContract
{
    /**
     * Create a new password broker instance.
     */
    public function __construct(
        protected ResetPasswordBroker $resetBroker,
        #[\SensitiveParameter] protected TokenRepositoryInterface $tokens,
        protected UserProvider $users,
        protected ?Dispatcher $events = null,
        protected Timebox $timebox = new Timebox,
        protected int $timeboxDuration = 200000
    ) {}

    public function sendSetLink(#[\SensitiveParameter] array $credentials, ?Closure $callback = null)
    {
        return $this->timebox->call(function () use ($credentials, $callback) {
            $user = $this->getUser($credentials);

            if (is_null($user)) {
                return static::INVALID_USER;
            }

            $token = $this->tokens->create($user);

            if ($callback) {
                return $callback($user, $token) ?? static::SET_LINK_SENT;
            }

            $user->sendPasswordSetNotification($token);

            $this->events?->dispatch(new PasswordSetLinkSent($user));

            return static::SET_LINK_SENT;
        }, $this->timeboxDuration);
    }

    /**
     * Set the password for the given token.
     *
     * @return string
     */
    public function set(#[\SensitiveParameter] array $credentials, Closure $callback)
    {
        return $this->timebox->call(function ($timebox) use ($credentials, $callback) {
            $user = $this->validateSet($credentials);

            if (! $user instanceof CanSetPasswordContract) {
                return $user;
            }

            $password = $credentials['password'];

            $callback($user, $password);

            $this->tokens->delete($user);

            $timebox->returnEarly();

            return static::PASSWORD_SET;
        }, $this->timeboxDuration);
    }

    /**
     * Validate a password set for the given credentials.
     *
     * @return CanSetPasswordContract|string
     */
    protected function validateSet(#[\SensitiveParameter] array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return static::INVALID_USER;
        }

        if (! $this->tokens->exists($user, $credentials['token'])) {
            return static::INVALID_TOKEN;
        }

        return $user;
    }

    /**
     * Get the user for the given credentials.
     *
     * @return CanSetPasswordContract|null
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(#[\SensitiveParameter] array $credentials)
    {
        $credentials = \Illuminate\Support\Arr::except($credentials, ['token']);

        $user = $this->users->retrieveByCredentials($credentials);

        if ($user && ! $user instanceof CanSetPasswordContract) {
            throw new \UnexpectedValueException('User must implement CanSetPassword interface.');
        }

        return $user;
    }

    public function sendResetLink(array $credentials, ?Closure $callback = null)
    {
        return $this->resetBroker->sendResetLink($credentials, $callback);
    }

    public function reset(#[\SensitiveParameter] array $credentials, Closure $callback)
    {
        return $this->resetBroker->reset($credentials, $callback);
    }

    /**
     * Create a new password set token for the given user.
     *
     * @return string
     */
    public function createSetToken(CanSetPasswordContract $user)
    {
        return $this->tokens->create($user);
    }

    /**
     * Create a new password reset token for the given user.
     *
     * @return string
     */
    public function createResetToken(CanResetPassword $user)
    {
        return $this->resetBroker->createToken($user);
    }
}
