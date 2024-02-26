<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Contracts;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;

interface CurrencyService
{
    public function getCurrency(int|string|Model|BackedEnum $currency): string;
}
