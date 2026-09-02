<?php

use Illuminate\Http\Request;
use Metafori\Core\Http\Middleware\SetPreferredLocale;
use Metafori\Core\Models\User;

it('sets the admin locale from their preferred locale', function () {
    $user = User::factory()->create(['preferred_locale' => 'sk']);
    $request = Request::create('/');
    $locale = null;

    $request->setUserResolver(fn (): User => $user);

    app(SetPreferredLocale::class)->handle($request, function (Request $request) use (&$locale) {
        $locale = app()->getLocale();

        return response()->noContent();
    });

    expect($locale)->toBe('sk');
});
