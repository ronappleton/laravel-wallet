<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Services;

use Appleton\LaravelWallet\Contracts\CurrencyService;
use Appleton\LaravelWallet\Services\Concerns\HasModels;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Currency implements CurrencyService
{
    use HasModels;

    public function getCurrency(int|string|Model|BackedEnum $currency): string
    {
        if (is_string($currency) && $this->isUuid($currency) || is_int($currency)) {
            return $this->fromId($currency);
        }

        if ($currency instanceof Model) {
            return $this->fromModel($currency);
        }

        if ($currency instanceof BackedEnum) {
            return $this->fromEnum($currency);
        }

        return $currency;
    }

    protected function fromId(int|string $currency): string
    {
        return $this->fromModel(
            $this->getModel()::query()->find($currency)
        );
    }

    protected function fromEnum(BackedEnum $currency): string
    {
        return (string) $currency->value;
    }

    protected function isUuid(string $currency): bool
    {
        return Str::isUuid($currency);
    }
}
