<?php

namespace App\Console\Commands;

use App\Services\ExchangeRate\ExchangeRateService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class SyncExchangeRates extends Command
{
    protected $signature = 'exchange:sync 
        {--base= : Базовая валюта (по умолчанию из конфигурации)} 
        {--symbols= : Список валют через запятую (по умолчанию из конфигурации)}';

    protected $description = 'Синхронизировать курсы валют с внешним API и сохранить в базу данных';

    public function handle(ExchangeRateService $service): int
    {
        $baseCurrency = $this->option('base');
        $symbols = $this->option('symbols')
            ? array_filter(array_map('trim', explode(',', $this->option('symbols'))))
            : null;

        try {
            $count = $service->syncLatest($baseCurrency, $symbols);
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        } catch (\Throwable $throwable) {
            $this->error('Не удалось синхронизировать курсы: '.$throwable->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Обновлено %d записей о курсах валют.', $count));

        return self::SUCCESS;
    }
}

