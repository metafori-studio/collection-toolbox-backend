<?php

namespace Metafori\Core\Http\Controllers\Api;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Metafori\Core\Auth\Events\PasswordSet;
use Metafori\Core\Support\Facades\Password;
use Metafori\Core\Traits\PasswordValidationRules;

class PasswordController extends Controller
{
    use PasswordValidationRules;

    public function forgot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker()->sendResetLink($validated);

        if ($status === Password::ResetLinkSent) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => $this->passwordRules(),
        ]);

        $status = Password::broker()->reset(
            $validated,
            function ($user, $password) {
                $this->updatePassword($user, $password);

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PasswordReset) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function set(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => $this->passwordRules(),
        ]);

        $status = Password::broker()->set(
            $validated,
            function ($user, $password) {
                $this->updatePassword($user, $password);

                event(new PasswordSet($user));
            }
        );

        if ($status === Password::PasswordSet) {
            return response()->json(['message' => __($status)]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    private function updatePassword($user, $password): void
    {
        $user->forceFill([
            'password' => $password,
        ])->setRememberToken(Str::random(60));

        $user->save();
    }
}
