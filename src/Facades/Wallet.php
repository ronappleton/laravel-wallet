<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Facades;

use Appleton\LaravelWallet\Contracts\WalletService as WalletContract;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection getWallets(?Model $owner, string|Model|BackedEnum|null $currency = null, ?string $name = null)
 * @method static Model getWallet(?Model $owner, string|Model|BackedEnum|null $currency = null, ?string $name = null)
 */
class Wallet extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WalletContract::class;
    }
}
