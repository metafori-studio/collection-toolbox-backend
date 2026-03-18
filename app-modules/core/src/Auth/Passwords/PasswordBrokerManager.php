<?php

namespace Metafori\Core\Auth\Passwords;

use Illuminate\Auth\Passwords\PasswordBrokerManager as BasePasswordBrokerManager;
use InvalidArgumentException;

class PasswordBrokerManager extends BasePasswordBrokerManager
{
    /**
     * Resolve the given broker.
     *
     * @param  string  $name
     * @return Contracts\PasswordBroker
     *
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Password broker driver [{$name}] is not defined.");
        }

        $resetDriver = $config['reset_driver'] ?? null;

        if ($resetDriver === null) {
            throw new InvalidArgumentException("Password broker [{$name}] must define a [reset_driver].");
        }

        return new PasswordBroker(
            parent::resolve($resetDriver),
            $this->createTokenRepository($config),
            $this->app['auth']->createUserProvider($config['provider'] ?? null),
            $this->app['events'] ?? null,
            timeboxDuration: $this->app['config']->get('auth.timebox_duration', 200000)
        );
    }
}
