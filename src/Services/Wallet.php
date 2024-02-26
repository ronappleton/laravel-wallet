<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Services;

use Appleton\LaravelWallet\Contracts\CurrencyService as CurrencyContract;
use Appleton\LaravelWallet\Contracts\WalletService as WalletContract;
use Appleton\LaravelWallet\Models\Concerns\HasWallets;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use RuntimeException;

class Wallet implements WalletContract
{
    public function __construct(private readonly CurrencyContract $currencyService)
    {
    }

    public function getWallets(Model $owner, int|string|Model|BackedEnum|null $currency = null, ?string $name = null
    ): Collection {
        if (! in_array(HasWallets::class, class_uses_recursive($owner::class))) {
            throw new RuntimeException('Owner model must implement HasWallets trait');
        }

        /** @phpstan-ignore-next-line */
        $wallets = $owner->wallets();

        if ($name) {
            $wallets = $wallets->where('name', $name);
        }

        if ($currency) {
            $wallets = $wallets->where('currency', $this->currencyService->getCurrency($currency));
        }

        return $wallets->get();
    }

    public function getWallet(Model $owner, int|string|Model|BackedEnum|null $currency = null, ?string $name = null
    ): Model|null {
        $wallets = $this->getWallets($owner, $currency, $name);

        if ($wallets->count() > 1) {
            throw new RuntimeException('More than one wallet found');
        }

        $wallet = $this->getWallets($owner, $currency, $name)->first();

        /** @var Model|null $wallet */
        return $wallet;
    }
}
