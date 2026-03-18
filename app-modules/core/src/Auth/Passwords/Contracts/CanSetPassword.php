<?php

namespace Metafori\Core\Auth\Passwords\Contracts;

interface CanSetPassword
{
    /**
     * Get the e-mail address where password set links are sent.
     */
    public function getEmailForPasswordSet(): string;

    /**
     * Send the password set notification.
     */
    public function sendPasswordSetNotification(#[\SensitiveParameter] string $token): void;
}
