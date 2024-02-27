<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Services\Concerns;

use Appleton\LaravelWallet\Exceptions\CurrencyAttributeNotSet;
use Appleton\LaravelWallet\Exceptions\CurrencyModelNotSet;
use Appleton\LaravelWallet\Exceptions\InvalidModel;
use Appleton\LaravelWallet\Exceptions\InvalidScalarType;
use Illuminate\Database\Eloquent\Model;

trait HasModels
{
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

    /**
     * @return class-string
     */
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