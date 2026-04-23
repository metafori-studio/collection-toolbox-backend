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
     * @return PasswordBroker
     *
     * @throws InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Password broker driver [{$name}] is not defined.");
        }

        return new PasswordBroker(
            parent::resolve($name),
            $this->app['hash'],
            timeboxDuration: $this->app['config']->get('auth.timebox_duration', 200000)
        );
    }
}
