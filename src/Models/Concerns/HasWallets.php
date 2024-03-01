<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models\Concerns;

use Appleton\LaravelWallet\Exceptions\WalletCreationRetriction;
use Appleton\LaravelWallet\Models\Wallet;
use Appleton\TypedConfig\Facades\TypedConfig as Config;
use BackedEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\UniqueConstraintViolationException;

trait HasWallets
{
    public function wallets(): MorphMany
    {
        return $this->morphMany(Config::classString('wallet.wallet_model', Wallet::class), 'ownable');
    }

    public function createWallet(string|BackedEnum $currency): Wallet
    {
        try {
            return $this->wallets()->create(['currency' => $currency instanceof BackedEnum ? $currency->value : $currency]);
        } catch (UniqueConstraintViolationException) {
            throw new WalletCreationRetriction(
                'Cannot create wallet, a wallet with this currency already exists.'
            );
        }
    }
}
