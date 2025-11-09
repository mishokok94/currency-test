<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string'],
        ]);

        $locale = $validated['locale'];
        $supported = Config::get('app.supported_locales', []);

        if (! in_array($locale, $supported, true)) {
            $locale = Config::get('app.locale');
        }

        $request->session()->put('locale', $locale);
        Cookie::queue(cookie()->forever('locale', $locale));

        return redirect()->back();
    }
}
