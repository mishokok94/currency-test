<?php

use App\Http\Controllers\CurrencyRatesController;
use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CurrencyRatesController::class, 'index'])->name('currency.index');
Route::post('/rates/sync', [CurrencyRatesController::class, 'sync'])->name('currency.sync');
Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/login', fn () => redirect()->route('filament.admin.auth.login'))->name('login');
