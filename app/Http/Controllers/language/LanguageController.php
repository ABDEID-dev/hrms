<?php

namespace App\Http\Controllers\language;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function swap($locale)
    {

        if (! in_array($locale, ['ar', 'en'])) {
            abort(400);
        } else {
            session()->put('locale', $locale);
        }

        App::setLocale($locale);

        return redirect()
            ->back()
            ->withCookie(Cookie::make('preferred_locale', $locale, 60 * 24 * 365));
    }
}
