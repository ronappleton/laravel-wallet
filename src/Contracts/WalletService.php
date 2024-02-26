<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface WalletService
{
    public function getWallets(Model $owner, int|string|Model|\BackedEnum|null $currency = null, ?string $name = null): Collection;

    public function getWallet(Model $owner, int|string|Model|\BackedEnum|null $currency = null, ?string $name = null): ?Model;
}
