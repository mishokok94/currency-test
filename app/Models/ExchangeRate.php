<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Scope;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon $observed_at
 * @property string $base_currency
 * @property string $target_currency
 * @property float $rate
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|ExchangeRate latest(string $column = null)
 * @method static Builder|ExchangeRate query()
 */
class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'observed_at',
        'base_currency',
        'target_currency',
        'rate',
    ];

    protected $casts = [
        'observed_at' => 'date',
        'rate' => 'float',
    ];
}

