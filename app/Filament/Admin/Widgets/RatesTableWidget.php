<?php

namespace App\Filament\Admin\Widgets;

use App\Models\ExchangeRate;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RatesTableWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $buyMargin = config('exchange.margins.buy', 0.005);
        $sellMargin = config('exchange.margins.sell', 0.005);

        return $table
            ->heading(__('currency.title'))
            ->query(fn (): Builder => ExchangeRate::query()->orderByDesc('observed_at'))
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('observed_at')
                    ->label(__('currency.columns.date'))
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('target_currency')
                    ->label(__('currency.columns.currency'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('rate')
                    ->label('Base rate (MDL)')
                    ->numeric(4)
                    ->sortable(),
                TextColumn::make('buy')
                    ->label(__('currency.columns.buy'))
                    ->numeric(4)
                    ->getStateUsing(fn (ExchangeRate $record) => round($record->rate * (1 - $buyMargin), 4)),
                TextColumn::make('sell')
                    ->label(__('currency.columns.sell'))
                    ->numeric(4)
                    ->getStateUsing(fn (ExchangeRate $record) => round($record->rate * (1 + $sellMargin), 4)),
            ])
            ->filters([
                SelectFilter::make('target_currency')
                    ->label(__('currency.filters.currency'))
                    ->options(fn () => ExchangeRate::query()
                        ->orderBy('target_currency')
                        ->pluck('target_currency', 'target_currency')
                        ->toArray(),
                    ),
                Filter::make('observed_at_range')
                    ->label(__('currency.filters.date_range_label'))
                    ->form([
                        DatePicker::make('from')
                            ->label(__('currency.filters.date_from')),
                        DatePicker::make('until')
                            ->label(__('currency.filters.date_to')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('observed_at', '>=', $date))
                            ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('observed_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        if (! $from && ! $until) {
                            return null;
                        }

                        $fromLabel = $from ? CarbonImmutable::parse($from)->format('d M Y') : '...';
                        $untilLabel = $until ? CarbonImmutable::parse($until)->format('d M Y') : '...';

                        return __('currency.filters.date_range', ['from' => $fromLabel, 'to' => $untilLabel]);
                    }),
            ])
            ->recordActions([])
            ->bulkActions([]);
    }
}
