<?php

return [
    'title' => 'Exchange rates against the Moldovan leu',
    'updated' => 'Updated: :date',
    'updated_unknown' => 'Updated date not available',
    'refresh_button' => 'Refresh rates',
    'columns' => [
        'date' => 'Date',
        'currency' => 'Currency',
        'buy' => 'Buy (MDL)',
        'sell' => 'Sell (MDL)',
    ],
    'no_data' => 'No data to display.',
    'filters' => [
        'title' => 'Filters',
        'currency' => 'Currency',
        'all' => 'All currencies',
        'date_from' => 'Date from',
        'date_to' => 'Date to',
        'apply' => 'Apply filters',
        'reset' => 'Reset',
        'date_range_label' => 'Date range',
        'date_range' => ':from — :to',
    ],
    'calculator' => [
        'title' => 'Currency calculator',
        'hint' => 'Convert an amount into MDL using the current rates.',
        'currency' => 'Currency',
        'amount' => 'Amount',
        'buy_total' => 'Purchase total',
        'sell_total' => 'Sale total',
    ],
    'navigation_label' => 'Exchange rates',
    'navigation_group' => 'Analytics',
    'sync_success' => 'Rates updated. :count records loaded.',
    'sync_error_key' => 'Unable to synchronise rates. Check your API key.',
    'sync_error_generic' => 'Exchange rate service is unavailable. Please try again later.',
];
