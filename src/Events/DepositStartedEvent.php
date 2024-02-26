<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Events;

use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel as Wallet;
use Illuminate\Foundation\Events\Dispatchable;

class DepositStartedEvent
{
    use Dispatchable;

    public function __construct(
        private readonly Wallet $wallet,
        private readonly float $amount,
        private readonly string $description,
        private readonly array|WalletMeta $meta,
    ) {
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getMeta(): array|WalletMeta
    {
        return $this->meta;
    }
}
