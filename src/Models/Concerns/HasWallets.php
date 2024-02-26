<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models\Concerns;

use Appleton\LaravelWallet\Models\Wallet;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasWallets
{
    public function wallets(): MorphMany
    {
        return $this->morphMany(config('wallet.models.wallet', Wallet::class), 'ownable');
    }
}
