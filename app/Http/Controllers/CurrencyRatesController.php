<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExchangeRateFilterRequest;
use App\Services\ExchangeRate\ExchangeRateService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class CurrencyRatesController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
    ) {
    }

    public function index(ExchangeRateFilterRequest $request): View
    {
        $validated = $request->validated();

        $filters = [
            'currency' => $validated['currency'] ?? null,
            'date_from' => isset($validated['date_from']) ? CarbonImmutable::parse($validated['date_from']) : null,
            'date_to' => isset($validated['date_to']) ? CarbonImmutable::parse($validated['date_to']) : null,
        ];

        $perPage = (int) config('exchange.display.per_page', 5);
        $priority = config('exchange.display.priority', []);

        $rates = $this->exchangeRateService
            ->filteredRatesPaginator($filters, $perPage, $priority)
            ->withQueryString();

        $firstItem = $rates->first();
        $lastUpdated = $firstItem['observed_at'] ?? null;
        $lastUpdatedFormatted = $lastUpdated ? $lastUpdated->translatedFormat('d.m.Y') : null;

        $calculatorRates = $this->exchangeRateService
            ->latestRatesCollection($priority)
            ->mapWithKeys(fn (array $rate) => [
                $rate['currency'] => [
                    'buy' => $rate['buy'],
                    'sell' => $rate['sell'],
                ],
            ]);

        $availableCurrencies = $this->exchangeRateService
            ->availableCurrencies()
            ->values()
            ->all();

        return view('currency.index', [
            'rates' => $rates,
            'lastUpdated' => $lastUpdatedFormatted,
            'filters' => [
                'currency' => $filters['currency'] ?? null,
                'date_from' => $filters['date_from']?->format('Y-m-d'),
                'date_to' => $filters['date_to']?->format('Y-m-d'),
            ],
            'availableCurrencies' => $availableCurrencies,
            'calculatorRates' => $calculatorRates,
        ]);
    }

    public function sync(): RedirectResponse
    {
        try {
            $count = $this->exchangeRateService->syncLatest();

            return redirect()
                ->route('currency.index')
                ->with('status', __('currency.sync_success', ['count' => $count]));
        } catch (ValidationException $exception) {
            return redirect()
                ->route('currency.index')
                ->with('error', __('currency.sync_error_key'));
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()
                ->route('currency.index')
                ->with('error', __('currency.sync_error_generic'));
        }
    }
}

