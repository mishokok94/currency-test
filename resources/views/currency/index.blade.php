@extends('layouts.app')

@section('title', __('currency.title'))

@section('content')
    <div class="page-header">
        <div>
            <h1>{{ __('currency.title') }}</h1>
            <p class="meta">
                @if($lastUpdated)
                    {{ __('currency.updated', ['date' => $lastUpdated]) }}
                @else
                    {{ __('currency.updated_unknown') }}
                @endif
            </p>
        </div>

        <form method="POST" action="{{ route('currency.sync') }}" class="sync-form">
            @csrf
            <button type="submit" class="sync-button">{{ __('currency.refresh_button') }}</button>
        </form>
    </div>

    <div class="content-layout">
        <div class="main-column">
            <form method="GET" action="{{ route('currency.index') }}" class="filters">
                <fieldset class="filters__group">
                    <legend>{{ __('currency.filters.title') }}</legend>

                    <div class="filters__field">
                        <label for="currency-filter">{{ __('currency.filters.currency') }}</label>
                        <select id="currency-filter" name="currency">
                            <option value="">{{ __('currency.filters.all') }}</option>
                            @foreach($availableCurrencies as $currency)
                                <option value="{{ $currency }}" @selected($filters['currency'] === $currency)>
                                    {{ $currency }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filters__field">
                        <label for="date-from">{{ __('currency.filters.date_from') }}</label>
                        <input type="date" id="date-from" name="date_from" value="{{ $filters['date_from'] }}">
                    </div>

                    <div class="filters__field">
                        <label for="date-to">{{ __('currency.filters.date_to') }}</label>
                        <input type="date" id="date-to" name="date_to" value="{{ $filters['date_to'] }}">
                    </div>
                </fieldset>

                <div class="filters__actions">
                    <button type="submit" class="filters__apply">{{ __('currency.filters.apply') }}</button>
                    <a href="{{ route('currency.index') }}" class="filters__reset">{{ __('currency.filters.reset') }}</a>
                </div>
            </form>

            @if(session('status'))
                <div class="alert alert--success">{{ session('status') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert--error">{{ session('error') }}</div>
            @endif

            <table role="table" class="currency-table">
                <thead>
                <tr>
                    <th scope="col">{{ __('currency.columns.date') }}</th>
                    <th scope="col">{{ __('currency.columns.currency') }}</th>
                    <th scope="col">{{ __('currency.columns.buy') }}</th>
                    <th scope="col">{{ __('currency.columns.sell') }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rates as $rate)
                    <tr>
                        <td data-label="{{ __('currency.columns.date') }}">
                            {{ $rate['observed_at']->translatedFormat('d.m.Y') }}
                        </td>
                        <td data-label="{{ __('currency.columns.currency') }}">
                            <span class="currency-code">{{ $rate['currency'] }}</span>
                        </td>
                        <td data-label="{{ __('currency.columns.buy') }}" class="rate">
                            {{ number_format($rate['buy'], 4, '.', ' ') }}
                        </td>
                        <td data-label="{{ __('currency.columns.sell') }}" class="rate">
                            {{ number_format($rate['sell'], 4, '.', ' ') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">{{ __('currency.no_data') }}</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            @if($rates->hasPages())
                <div class="pagination-wrapper">
                    {{ $rates->links() }}
                </div>
            @endif
        </div>

        <aside class="calculator">
            <h2>{{ __('currency.calculator.title') }}</h2>
            <p class="calculator__hint">{{ __('currency.calculator.hint') }}</p>

            <div class="calculator__field">
                <label for="calculator-currency">{{ __('currency.calculator.currency') }}</label>
                <select id="calculator-currency" data-calculator="currency">
                    @foreach($calculatorRates as $currency => $rate)
                        <option value="{{ $currency }}">{{ $currency }}</option>
                    @endforeach
                </select>
            </div>

            <div class="calculator__field">
                <label for="calculator-amount">{{ __('currency.calculator.amount') }}</label>
                <input type="number" id="calculator-amount" min="0" step="0.01" data-calculator="amount" placeholder="100">
            </div>

            <div class="calculator__results">
                <div class="calculator__result">
                    <span class="calculator__label">{{ __('currency.calculator.buy_total') }}</span>
                    <strong data-calculator="buy">--</strong>
                    <span class="calculator__suffix">MDL</span>
                </div>
                <div class="calculator__result">
                    <span class="calculator__label">{{ __('currency.calculator.sell_total') }}</span>
                    <strong data-calculator="sell">--</strong>
                    <span class="calculator__suffix">MDL</span>
                </div>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const rates = @json($calculatorRates);
            const currencySelect = document.querySelector('[data-calculator="currency"]');
            const amountInput = document.querySelector('[data-calculator="amount"]');
            const buyOutput = document.querySelector('[data-calculator="buy"]');
            const sellOutput = document.querySelector('[data-calculator="sell"]');

            if (!currencySelect || !amountInput || !buyOutput || !sellOutput) {
                return;
            }

            function format(value) {
                return Number.isFinite(value) ? value.toFixed(2) : '--';
            }

            function update() {
                const selectedCurrency = currencySelect.value;
                const rate = rates[selectedCurrency];
                const amount = parseFloat(amountInput.value);

                if (!rate || !Number.isFinite(amount)) {
                    buyOutput.textContent = '--';
                    sellOutput.textContent = '--';
                    return;
                }

                buyOutput.textContent = format(amount * rate.buy);
                sellOutput.textContent = format(amount * rate.sell);
            }

            currencySelect.addEventListener('change', update);
            amountInput.addEventListener('input', update);

            update();
        })();
    </script>
@endpush

