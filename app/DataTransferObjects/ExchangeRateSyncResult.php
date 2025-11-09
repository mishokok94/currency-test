<?php

namespace App\DataTransferObjects;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

/**
 * @property-read Collection<int, ExchangeRateData> $rates
 */
class ExchangeRateSyncResult extends Data
{
    /**
     * @param Collection<int, ExchangeRateData> $rates
     */
    public function __construct(
        public readonly CarbonImmutable $observedAt,
        public readonly Collection $rates,
    ) {
    }
}

