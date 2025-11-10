<?php

namespace App\Services\ExchangeRate;

use App\DataTransferObjects\ExchangeRateData;
use App\DataTransferObjects\ExchangeRateSyncResult;
use App\Models\ExchangeRate;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\RequestException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ExchangeRateService
{
    private ?CarbonImmutable $latestDateCache = null;

    /**
     * Sync the latest rates and persist them in the database.
     */
    public function syncLatest(?string $baseCurrency = null, ?array $symbols = null): int
    {
        $result = $this->fetchLatest($baseCurrency, $symbols);

        $this->latestDateCache = null;

        return $this->store($result);
    }

    /**
     * Fetch the latest rates from the external API.
     */
    public function fetchLatest(?string $baseCurrency = null, ?array $symbols = null): ExchangeRateSyncResult
    {
        $baseCurrency = strtoupper($baseCurrency ?? config('exchange.api.base_currency'));
        $symbols = collect($symbols ?? config('exchange.api.symbols'))
            ->map(fn (string $symbol) => strtoupper(trim($symbol)))
            ->filter()
            ->values();

        if (empty(config('exchange.api.api_key'))) {
            throw ValidationException::withMessages([
                'exchange_rate_api_key' => 'Missing API key for the exchange rate service.',
            ]);
        }

        $endpoint = sprintf(
            '%s/%s/latest/%s',
            rtrim(config('exchange.api.base_url'), '/'),
            config('exchange.api.api_key'),
            $baseCurrency
        );

        try {
            $response = Http::retry(2, 100)
                ->acceptJson()
                ->get($endpoint)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            Log::error('Failed to fetch exchange rates', [
                'endpoint' => $endpoint,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            throw $exception;
        }

        if (($response['result'] ?? null) !== 'success') {
            Log::warning('Exchange rates API returned a non-successful result', [
                'endpoint' => $endpoint,
                'response' => $response,
            ]);

            throw ValidationException::withMessages([
                'exchange_rate_api' => 'Exchange rate API returned an error: '.data_get($response, 'error-type', 'unknown_error'),
            ]);
        }

        $timestamp = (int) ($response['time_last_update_unix'] ?? time());
        $observedAt = CarbonImmutable::createFromTimestampUTC($timestamp)->startOfDay();

        $rates = $symbols->map(function (string $symbol) use ($response, $baseCurrency, $observedAt) {
            $rawRate = data_get($response, "conversion_rates.$symbol");

            if (! is_numeric($rawRate) || (float) $rawRate <= 0.0) {
                return null;
            }

            $mdlPerUnit = 1 / (float) $rawRate;

            return new ExchangeRateData(
                observedAt: $observedAt,
                baseCurrency: $baseCurrency,
                targetCurrency: $symbol,
                rate: $mdlPerUnit
            );
        })->filter()->values();

        return new ExchangeRateSyncResult(
            observedAt: $observedAt,
            rates: $rates,
        );
    }

    public function store(ExchangeRateSyncResult $result): int
    {
        if ($result->rates->isEmpty()) {
            return 0;
        }

        $payload = $result->rates
            ->map(fn (ExchangeRateData $data) => $data->toUpsertPayload())
            ->all();

        ExchangeRate::query()->upsert(
            $payload,
            ['observed_at', 'base_currency', 'target_currency'],
            ['rate', 'updated_at']
        );

        return count($payload);
    }

    /**
     * Build a paginator with the applied filters and ordering.
     *
     * @param  array{currency?: ?string, date_from?: ?CarbonImmutable, date_to?: ?CarbonImmutable}  $filters
     */
    public function filteredRatesPaginator(array $filters, int $perPage, array $priority = []): LengthAwarePaginator
    {
        $query = $this->buildFilteredQuery($filters, $priority);
        $buyMargin = config('exchange.margins.buy', 0.005);
        $sellMargin = config('exchange.margins.sell', 0.005);

        return $query->paginate($perPage)->through(
            fn (ExchangeRate $rate) => $this->formatRate($rate, $buyMargin, $sellMargin)
        );
    }

    /**
     * Return a collection of rates for the latest available date (for calculators, lists, etc.).
     */
    public function latestRatesCollection(array $priority = []): Collection
    {
        $filters = ['currency' => null, 'date_from' => null, 'date_to' => null];
        $query = $this->buildFilteredQuery($filters, $priority);
        $buyMargin = config('exchange.margins.buy', 0.005);
        $sellMargin = config('exchange.margins.sell', 0.005);

        return $query->get()->map(
            fn (ExchangeRate $rate) => $this->formatRate($rate, $buyMargin, $sellMargin)
        );
    }

    public function availableCurrencies(): Collection
    {
        return ExchangeRate::query()
            ->select('target_currency')
            ->distinct()
            ->orderBy('target_currency')
            ->pluck('target_currency');
    }

    private function buildFilteredQuery(array $filters, array $priority = []): Builder
    {
        $query = ExchangeRate::query();

        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $currency = $filters['currency'] ?? null;

        if ($currency) {
            $query->where('target_currency', $currency);
        }

        if ($dateFrom) {
            $query->whereDate('observed_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('observed_at', '<=', $dateTo);
        }

        if (! $dateFrom && ! $dateTo) {
            $latestDate = $this->resolveLatestDate();

            if ($latestDate) {
                $query->whereDate('observed_at', $latestDate);
            }
        }

        if (! empty($priority)) {
            $query->orderByRaw($this->buildPriorityOrderClause($priority));
        }

        return $query
            ->orderByDesc('observed_at')
            ->orderBy('target_currency');
    }

    private function formatRate(ExchangeRate $rate, float $buyMargin, float $sellMargin): array
    {
        $baseRate = (float) $rate->rate;
        $observedAt = CarbonImmutable::parse($rate->observed_at);

        return [
            'currency' => $rate->target_currency,
            'buy' => round($baseRate * (1 - $buyMargin), 4),
            'sell' => round($baseRate * (1 + $sellMargin), 4),
            'base_rate' => $baseRate,
            'observed_at' => $observedAt,
        ];
    }

    private function resolveLatestDate(): ?CarbonImmutable
    {
        if ($this->latestDateCache !== null) {
            return $this->latestDateCache;
        }

        $rawDate = ExchangeRate::query()->max('observed_at');

        $this->latestDateCache = $rawDate ? CarbonImmutable::parse($rawDate) : null;

        return $this->latestDateCache;
    }

    private function buildPriorityOrderClause(array $priority): string
    {
        $cases = collect($priority)
            ->map(fn (string $symbol, int $index) => sprintf('WHEN "%s" THEN %d', addslashes($symbol), $index))
            ->implode(' ');

        $fallback = count($priority);

        return sprintf('CASE target_currency %s ELSE %d END', $cases, $fallback);
    }
}

