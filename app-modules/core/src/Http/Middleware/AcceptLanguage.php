<?php

namespace Metafori\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class AcceptLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locales = config('app.locales', [config('app.fallback_locale')]);

        $locale = $request->getPreferredLanguage($locales);

        if ($locale) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
