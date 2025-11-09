<?php

namespace App\DataTransferObjects;

use App\Models\ExchangeRate;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class ExchangeRateData extends Data
{
    public function __construct(
        public readonly CarbonImmutable $observedAt,
        public readonly string $baseCurrency,
        public readonly string $targetCurrency,
        public readonly float $rate,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toUpsertPayload(): array
    {
        return [
            'observed_at' => $this->observedAt->toDateString(),
            'base_currency' => $this->baseCurrency,
            'target_currency' => $this->targetCurrency,
            'rate' => $this->rate,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public static function fromModel(ExchangeRate $exchangeRate): self
    {
        return new self(
            observedAt: CarbonImmutable::parse($exchangeRate->observed_at),
            baseCurrency: $exchangeRate->base_currency,
            targetCurrency: $exchangeRate->target_currency,
            rate: (float) $exchangeRate->rate,
        );
    }
}

