<?php

namespace Metafori\Core\Support\Facades;

use Illuminate\Support\Facades\Password as BasePassword;
use Metafori\Core\Auth\Passwords\Contracts\PasswordBroker;

/**
 * @method static \Metafori\Core\Auth\Passwords\Contracts\PasswordBroker broker(string|null $name = null)
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
     *
     * @var string
     */
    const SetLinkSent = PasswordBroker::SET_LINK_SENT;

    /**
     * Constant representing a successfully set password.
     *
     * @var string
     */
    const PasswordSet = PasswordBroker::PASSWORD_SET;
}
