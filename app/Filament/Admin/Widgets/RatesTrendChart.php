<?php

namespace App\Filament\Admin\Widgets;

use App\Models\ExchangeRate;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class RatesTrendChart extends ChartWidget
{
    protected ?string $heading = '30-day trend';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'default' => 12,
        'xl' => 6,
    ];

    protected function getData(): array
    {
        $priorityCurrencies = config('exchange.display.priority', []);
        $currency = $priorityCurrencies[0] ?? ExchangeRate::query()
            ->select('target_currency')
            ->orderBy('target_currency')
            ->value('target_currency');

        if (! $currency) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $buyMargin = config('exchange.margins.buy', 0.005);
        $sellMargin = config('exchange.margins.sell', 0.005);

        $records = ExchangeRate::query()
            ->where('target_currency', $currency)
            ->orderByDesc('observed_at')
            ->limit(30)
            ->get()
            ->sortBy('observed_at');

        $labels = [];
        $buyData = [];
        $sellData = [];

        foreach ($records as $record) {
            $labels[] = CarbonImmutable::parse($record->observed_at)->format('d M');
            $buyData[] = round($record->rate * (1 - $buyMargin), 4);
            $sellData[] = round($record->rate * (1 + $sellMargin), 4);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('currency.columns.buy').' ('.$currency.')',
                    'data' => $buyData,
                    'borderColor' => '#2563eb',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.3)',
                    'tension' => 0.3,
                ],
                [
                    'label' => __('currency.columns.sell').' ('.$currency.')',
                    'data' => $sellData,
                    'borderColor' => '#f97316',
                    'backgroundColor' => 'rgba(249, 115, 22, 0.3)',
                    'tension' => 0.3,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
