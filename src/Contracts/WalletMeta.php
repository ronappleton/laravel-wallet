<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface WalletMeta extends Arrayable
{
    public function setFromWalletId(int|string $fromWalletId): self;

    public function setToWalletId(int|string $toWalletId): self;

    public function setMeta(string $key, mixed $value): self;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function setMetas(array $meta): self;
}
