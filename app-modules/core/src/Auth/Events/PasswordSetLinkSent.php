<?php

namespace Metafori\Core\Auth\Events;

use Illuminate\Queue\SerializesModels;

class PasswordSetLinkSent
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \Metafori\Core\Auth\Passwords\Contracts\CanSetPassword  $user  The user instance.
     */
    public function __construct(
        public $user,
    ) {}
}
