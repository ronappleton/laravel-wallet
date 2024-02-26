<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Events;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel as Wallet;
use Illuminate\Foundation\Events\Dispatchable;

class ConversionCompletedEvent
{
    use Dispatchable;

    public function __construct(
        private readonly Wallet $fromWallet,
        private readonly Wallet $toWallet,
        private readonly float $amount,
        private readonly WalletMeta $meta,
        private readonly CurrencyConverter $converter,
    ) {
    }

    public function getFromWallet(): Wallet
    {
        return $this->fromWallet;
    }

    public function getToWallet(): Wallet
    {
        return $this->toWallet;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getMeta(): WalletMeta
    {
        return $this->meta;
    }

    public function getConverter(): CurrencyConverter
    {
        return $this->converter;
    }
}
