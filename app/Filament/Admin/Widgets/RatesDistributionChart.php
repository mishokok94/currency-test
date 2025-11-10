<?php

namespace App\Filament\Admin\Widgets;

use App\Models\ExchangeRate;
use Filament\Widgets\ChartWidget;

class RatesDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Share of latest buy rates';

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
        $data = [];

        foreach ($records as $record) {
            $labels[] = $record->target_currency;
            $data[] = round($record->rate * (1 - $buyMargin), 4);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('currency.columns.buy'),
                    'data' => $data,
                    'backgroundColor' => [
                        '#2563eb',
                        '#f97316',
                        '#22c55e',
                        '#ec4899',
                        '#a855f7',
                        '#eab308',
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
