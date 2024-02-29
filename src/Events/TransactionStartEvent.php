<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Events;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionStartEvent
{
    use Dispatchable;

    /**
     * @param array<string, mixed>|WalletMeta $meta
     */
    public function __construct(
        private readonly TransactionType $type,
        private readonly float $amount,
        private readonly array|WalletMeta $meta = [],
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

    /**
     * @return array<string, mixed>|WalletMeta
     */
    public function getMeta(): array|WalletMeta
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
