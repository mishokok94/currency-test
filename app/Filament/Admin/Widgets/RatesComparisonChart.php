<?php

namespace App\Filament\Admin\Widgets;

use App\Models\ExchangeRate;
use Filament\Widgets\ChartWidget;

class RatesComparisonChart extends ChartWidget
{
    protected ?string $heading = 'Latest day comparison';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'default' => 12,
        'xl' => 3,
    ];

    protected function getData(): array
    {
        $latestDate = ExchangeRate::query()->max('observed_at');

        if (! $latestDate) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $buyMargin = config('exchange.margins.buy', 0.005);
        $sellMargin = config('exchange.margins.sell', 0.005);

        $prioritized = config('exchange.display.priority', []);

        $records = ExchangeRate::query()
            ->whereDate('observed_at', $latestDate)
            ->when(! empty($prioritized), fn ($query) => $query->whereIn('target_currency', $prioritized))
            ->orderBy('target_currency')
            ->get();

        if ($records->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => [],
            ];
        }

        $labels = [];
        $buy = [];
        $sell = [];

        foreach ($records as $record) {
            $labels[] = $record->target_currency;
            $buy[] = round($record->rate * (1 - $buyMargin), 4);
            $sell[] = round($record->rate * (1 + $sellMargin), 4);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('currency.columns.buy'),
                    'data' => $buy,
                    'backgroundColor' => '#2563eb',
                ],
                [
                    'label' => __('currency.columns.sell'),
                    'data' => $sell,
                    'backgroundColor' => '#f97316',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
