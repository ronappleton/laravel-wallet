<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Events;

use Appleton\LaravelWallet\Contracts\WalletModel as Wallet;
use Illuminate\Foundation\Events\Dispatchable;

class TransferStartedEvent
{
    use Dispatchable;

    public function __construct(
        private readonly Wallet $fromWallet,
        private readonly Wallet $toWallet,
        private readonly float $amount,
        private readonly array $meta,
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

    public function getMeta(): array
    {
        return $this->meta;
    }
}
