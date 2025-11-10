<?php

namespace App\Filament\Admin\Resources\ExchangeRates\Pages;

use App\Filament\Admin\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Resources\Pages\ListRecords;

class ListExchangeRates extends ListRecords
{
    protected static string $resource = ExchangeRateResource::class;
}
