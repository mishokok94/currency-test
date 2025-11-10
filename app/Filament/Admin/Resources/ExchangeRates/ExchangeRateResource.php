<?php

namespace App\Filament\Admin\Resources\ExchangeRates;

use App\Filament\Admin\Resources\ExchangeRates\Pages\ListExchangeRates;
use App\Filament\Admin\Resources\ExchangeRates\Schemas\ExchangeRateForm;
use App\Filament\Admin\Resources\ExchangeRates\Schemas\ExchangeRateInfolist;
use App\Filament\Admin\Resources\ExchangeRates\Tables\ExchangeRatesTable;
use App\Models\ExchangeRate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ExchangeRateResource extends Resource
{
    /**
     * The associated Eloquent model.
     */
    protected static ?string $model = ExchangeRate::class;

    /**
     * Icon used in the Filament sidebar navigation.
     */
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    /**
     * Custom label for the navigation menu.
     */
    public static function getNavigationLabel(): string
    {
        return __('currency.navigation_label');
    }

    /**
     * Custom navigation group.
     */
    public static function getNavigationGroup(): ?string
    {
        return __('currency.navigation_group');
    }

    /**
     * Form schema definition.
     */
    public static function form(Schema $schema): Schema
    {
        return ExchangeRateForm::configure($schema);
    }

    /**
     * Infolist schema definition.
     */
    public static function infolist(Schema $schema): Schema
    {
        return ExchangeRateInfolist::configure($schema);
    }

    /**
     * Table schema definition.
     */
    public static function table(Table $table): Table
    {
        return ExchangeRatesTable::configure($table);
    }

    /**
     * Related models (none yet).
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Resource pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => ListExchangeRates::route('/'),
        ];
    }
}
