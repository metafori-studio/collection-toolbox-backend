<?php

namespace Metafori\Core\Support;

use Illuminate\Support\Uri;
use Metafori\Core\Models\User;

class Frontend
{
    public function url(): string
    {
        return config('frontend.url');
    }

    /**
     * Get the URL to the frontend's password reset page.
     */
    public function resetPasswordUrl(User $user, string $token): string
    {
        return (string) Uri::of($this->url())
            ->withPath(config('frontend.routes.reset_password'))
            ->withQuery([
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
    }

    /**
     * Get the URL to the frontend's set password page.
     */
    public function setPasswordUrl(User $user, string $token): string
    {
        return (string) Uri::of($this->url())
            ->withPath(config('frontend.routes.set_password'))
            ->withQuery([
                'token' => $token,
                'email' => $user->getEmailForPasswordSet(),
            ]);
    }
}
