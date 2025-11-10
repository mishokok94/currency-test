<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Widgets\RatesComparisonChart;
use App\Filament\Admin\Widgets\RatesDistributionChart;
use App\Filament\Admin\Widgets\RatesTableWidget;
use App\Filament\Admin\Widgets\RatesTrendChart;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class RateDashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Exchange Rates Dashboard';

    public function getWidgets(): array
    {
        return [
            RatesTrendChart::class,
            RatesComparisonChart::class,
            RatesDistributionChart::class,
            RatesTableWidget::class,
        ];
    }
}
