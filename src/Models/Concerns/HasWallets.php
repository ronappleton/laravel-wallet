<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models\Concerns;

use Appleton\LaravelWallet\Models\Wallet;
use Appleton\TypedConfig\Facades\TypedConfig as Config;
use BackedEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWallets
{
    public function wallets(): MorphMany
    {
        return $this->morphMany(Config::classString('wallet.wallet_model', Wallet::class), 'ownable');
    }

    public function createWallet(string|BackedEnum $currency): Wallet
    {
        ///@TODO This needs to validate when creating a wallet in case the setting to lock
        return $this->wallets()->create(['currency' => $currency instanceof BackedEnum ? $currency->value : $currency]);
    }
}
