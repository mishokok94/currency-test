# External API: Exchange Rate API (exchangeRate-API.com)

## Why ExchangeRate-API
- Бесплатный тариф с 1500 запросами в месяц и API-ключом.
- Отдаёт JSON с последним временем обновления и словарём курсов по валютам.
- Поддерживает исторические данные и дополнительные функции (конвертации, пары).
- Простая схема URL: `https://v6.exchangerate-api.com/v6/{API_KEY}/latest/{BASE}`.

## Target Endpoints
- `https://v6.exchangerate-api.com/v6/{API_KEY}/latest/{BASE}` — актуальные курсы.
- `https://v6.exchangerate-api.com/v6/{API_KEY}/history/{BASE}/{YEAR}/{MONTH}` — исторические данные (при необходимости).
- Request parameters:
  - `BASE` — базовая валюта (по умолчанию `MDL`).
  - `symbols` — фильтруем уже на нашей стороне, оставляя нужные валюты.

## Data Model
- Table `exchange_rates`:
  - `observed_at` (date) — rate date (can store as `date` since rates are daily).
  - `base_currency` (string 3) — base we requested.
  - `target_currency` (string 3) — currency code for the row.
  - `rate` (decimal) — numeric value of the rate.
  - Index on (`observed_at`, `base_currency`, `target_currency`) to prevent duplicates.
  - Standard timestamps.
- Potential auxiliary tables for caching currency metadata if needed later.

## Usage in the Project
- Artisan sync command will fetch the latest or historical window, upsert records while avoiding duplicates.
- Scheduler (e.g., hourly/daily) will call the command to keep data fresh.
- Frontend page:
  - Table with pagination and filters (date range, base currency, target currency).
  - Charts:
    - Line chart: rate trend for selected currency over time.
    - Bar chart: compare multiple currencies on a selected date.
    - Pie/donut: share of rates relative to a baseline (e.g., normalized).
- Admin (Filament):
  - CRUD for viewing rates, managing currency list, manual refresh trigger.

## Next Steps
1. Add env config (`EXCHANGE_BASE_CURRENCY`, `EXCHANGE_SYMBOLS`, etc.).
2. Create migration and model for `exchange_rates`.
3. Implement API client + data DTO using `spatie/laravel-data`.
4. Build Artisan command and scheduler entry.
5. Expose public page and Filament resources.
