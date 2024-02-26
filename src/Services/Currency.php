<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Services;

use Appleton\LaravelWallet\Contracts\CurrencyService;
use Appleton\LaravelWallet\Exceptions\CurrencyAttributeNotSet;
use Appleton\LaravelWallet\Exceptions\CurrencyModelNotSet;
use Appleton\LaravelWallet\Exceptions\InvalidModel;
use Appleton\LaravelWallet\Exceptions\InvalidScalarType;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Currency implements CurrencyService
{
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

    protected function fromModel(Model $currency): string
    {
        $attribute = config('wallet.models.currency.currency_attribute', 'currency');

        if (! is_scalar($attribute)) {
            throw new InvalidScalarType(
                'Currency attribute must be a scalar value in config/wallet.php'
            );
        }

        $attributeValue = $currency->getAttribute((string) $attribute);

        if (is_null($attributeValue)) {
            throw new CurrencyAttributeNotSet(
                'Currency attribute must be set in config/wallet.php'
            );
        }

        assert(is_string($attributeValue), 'Currency attribute must be a string or a number');

        return $attributeValue;
    }

    protected function fromEnum(BackedEnum $currency): string
    {
        return (string) $currency->value;
    }

    protected function isUuid(string $currency): bool
    {
        return Str::isUuid($currency);
    }

    protected function getModel(): string
    {
        $model = config('wallet.models.currency.model');

        if (! is_string($model) || ! class_exists($model)) {
            throw new CurrencyModelNotSet(
                'Currency model must be set in config/wallet.php'
            );
        }

        if (! is_subclass_of($model, Model::class)) {
            throw new InvalidModel(
                'Currency model must be an instance of Illuminate\Database\Eloquent\Model'
            );
        }

        return $model;
    }
}
