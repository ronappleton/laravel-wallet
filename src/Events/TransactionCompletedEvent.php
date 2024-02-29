<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Events;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionCompletedEvent
{
    use Dispatchable;

    public function __construct(
        private readonly TransactionType $type,
        private readonly float $amount,
        private readonly WalletMeta $meta,
        private readonly ?WalletModel $toWallet = null,
        private readonly ?CurrencyConverter $converter = null
    ) {
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getMeta(): WalletMeta
    {
        return $this->meta;
    }

    public function getToWallet(): ?WalletModel
    {
        return $this->toWallet;
    }

    public function getConverter(): ?CurrencyConverter
    {
        return $this->converter;
    }
}
