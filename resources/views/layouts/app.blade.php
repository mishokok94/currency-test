<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="{{ url('/') }}" class="brand">
            <img src="https://laravel.com/img/logomark.min.svg" alt="Laravel logo">
            <span>{{ config('app.name') }}</span>
        </a>

        <div class="header-actions">
            <div class="language-switch" aria-label="{{ __('app.language_label') }}">
                @foreach(config('app.supported_locales', []) as $locale)
                    <form method="POST" action="{{ route('locale.update') }}">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $locale }}">
                        <button
                            type="submit"
                            class="language-switch__button {{ app()->getLocale() === $locale ? 'is-active' : '' }}"
                        >
                            {{ __('app.languages.' . $locale) }}
                        </button>
                    </form>
                @endforeach
            </div>

            <div class="auth-link">
                <a href="{{ url('/admin/login') }}">{{ __('app.login_admin') }}</a>
            </div>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

@stack('scripts')
</body>
</html>

