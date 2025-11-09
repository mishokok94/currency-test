<?php

use Illuminate\Support\Str;

return [
    'api' => [
        'base_url' => env('EXCHANGE_RATE_API_BASE_URL', 'https://v6.exchangerate-api.com/v6'),
        'api_key' => env('EXCHANGE_RATE_API_KEY'),
        'base_currency' => strtoupper(env('EXCHANGE_RATE_BASE_CURRENCY', 'MDL')),
        'symbols' => collect(explode(',', (string) env('EXCHANGE_RATE_SYMBOLS', 'USD,EUR,RON,UAH,RUB,GBP,CHF,PLN,TRY,CAD')))
            ->map(fn (string $symbol) => strtoupper(trim($symbol)))
            ->filter(fn (string $symbol) => Str::length($symbol) === 3)
            ->values()
            ->all(),
    ],
    'margins' => [
        'buy' => (float) env('EXCHANGE_RATE_BUY_MARGIN', 0.005),
        'sell' => (float) env('EXCHANGE_RATE_SELL_MARGIN', 0.005),
    ],
    'display' => [
        'priority' => ['USD', 'EUR', 'UAH', 'RUB', 'CAD'],
        'per_page' => (int) env('EXCHANGE_RATE_PER_PAGE', 5),
    ],
];

