<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Events;

use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel as Wallet;
use Illuminate\Foundation\Events\Dispatchable;

class TransferCompletedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly Wallet $fromWallet,
        public readonly Wallet $toWallet,
        public readonly float $amount,
        public readonly WalletMeta $meta,
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
}
