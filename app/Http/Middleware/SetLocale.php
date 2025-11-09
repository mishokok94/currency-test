<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = Config::get('app.supported_locales', []);

        $locale = $request->session()->get('locale', $request->cookie('locale', Config::get('app.locale')));

        if (! in_array($locale, $supported, true)) {
            $locale = Config::get('app.locale');
        }

        App::setLocale($locale);

        $response = $next($request);

        Cookie::queue(cookie()->forever('locale', $locale));

        return $response;
    }
}
