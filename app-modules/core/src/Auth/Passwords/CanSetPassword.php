<?php

namespace Metafori\Core\Auth\Passwords;

use Metafori\Core\Notifications\QueuedSetPassword;

trait CanSetPassword
{
    /**
     * Get the e-mail address where password set links are sent.
     */
    public function getEmailForPasswordSet(): string
    {
        return $this->email;
    }

    /**
     * Send the password set notification.
     */
    public function sendPasswordSetNotification(#[\SensitiveParameter] string $token): void
    {
        $this->notify(new QueuedSetPassword($token));
    }
}
