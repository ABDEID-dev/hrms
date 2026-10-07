<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session()->get('locale');

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = $request->cookie('preferred_locale');
        }

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = 'ar';
        }

        session()->put('locale', $locale);
        app()->setLocale($locale);

        // Locale is enabled and allowed to be change
        return $next($request);
    }
}
