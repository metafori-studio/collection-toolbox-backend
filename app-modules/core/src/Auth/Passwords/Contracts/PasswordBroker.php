<?php

namespace Metafori\Core\Auth\Passwords\Contracts;

use Closure;
use Illuminate\Contracts\Auth\PasswordBroker as BasePasswordBroker;

interface PasswordBroker extends BasePasswordBroker
{
    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    const SET_LINK_SENT = 'passwords.sent_set';

    /**
     * Constant representing a successfully set password.
     *
     * @var string
     */
    const PASSWORD_SET = 'passwords.set';

    /**
     * Send a password set link to a user.
     *
     * @return string
     */
    public function sendSetLink(array $credentials, ?Closure $callback = null);

    /**
     * Set a user's password.
     *
     * @return mixed
     */
    public function set(array $credentials, Closure $callback);
}
