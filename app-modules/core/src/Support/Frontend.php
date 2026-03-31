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
        return $this->route('reset_password', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]);
    }

    /**
     * Get the URL to the frontend's set password page.
     */
    public function setPasswordUrl(User $user, string $token): string
    {
        return $this->route('set_password', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }

    protected function route(string $name, array $parameters = []): string
    {
        $path = config("frontend.routes.{$name}");
        $query = [];

        foreach ($parameters as $key => $value) {
            $search = "{{$key}}";

            if (\is_string($key) && \str_contains($path, $search)) {
                $path = \str_replace($search, \urlencode((string) $value), $path);
            } else {
                $query[$key] = (string) $value;
            }
        }

        if (\preg_match('/\{([^}]+)\}/', $path, $matches)) {
            throw new \InvalidArgumentException("Missing frontend route parameter [{$matches[1]}].");
        }

        $uri = Uri::of($this->url())->withPath($path);

        return (string) $uri->withQuery($query);
    }
}
