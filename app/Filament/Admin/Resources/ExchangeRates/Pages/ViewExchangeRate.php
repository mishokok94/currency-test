<?php

namespace App\Filament\Admin\Resources\ExchangeRates\Pages;

use App\Filament\Admin\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewExchangeRate extends ViewRecord
{
    protected static string $resource = ExchangeRateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
