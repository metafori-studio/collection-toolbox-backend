<?php

namespace Metafori\Core\Auth\Events;

use Illuminate\Queue\SerializesModels;

class PasswordSet
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \Metafori\Core\Auth\Passwords\Contracts\CanSetPassword  $user
     */
    public function __construct(
        public $user,
    ) {}
}
