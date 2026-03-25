<?php

namespace Metafori\Core\Support\Facades;

use Illuminate\Support\Facades\Password as BasePassword;
use Metafori\Core\Auth\Passwords\PasswordBroker;

/**
 * @method static \Metafori\Core\Auth\Passwords\PasswordBroker broker(string|null $name = null)
 * @method static string sendSetLink(array $credentials, \Closure|null $callback = null)
 * @method static mixed set(array $credentials, \Closure $callback)
 *
 * @see \Metafori\Core\Auth\Passwords\PasswordBrokerManager
 * @see PasswordBroker
 */
class Password extends BasePassword
{
    /**
     * Constant representing a successfully sent password set email.
     */
    public const string SET_LINK_SENT = PasswordBroker::SET_LINK_SENT;

    /**
     * Constant representing a successfully set password.
     */
    public const string PASSWORD_SET = PasswordBroker::PASSWORD_SET;
}
