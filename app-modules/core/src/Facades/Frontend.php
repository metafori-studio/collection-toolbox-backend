<?php

namespace Metafori\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string resetPasswordUrl(\Metafori\Core\Models\User $user, string $token)
 *
 * @see \Metafori\Core\Support\Frontend
 */
class Frontend extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'frontend';
    }
}
