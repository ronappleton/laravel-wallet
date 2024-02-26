<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Helpers;

use Appleton\LaravelWallet\Contracts\WalletMeta as WalletMetaContract;
use Appleton\LaravelWallet\Exceptions\MetaKeyExists;

class WalletMeta implements WalletMetaContract
{
    protected int|string|null $fromWalletId = null;

    protected int|string|null $toWalletId = null;

    /**
     * @var array <string, \Illuminate\Foundation\Auth\User|int|string|null>
     */
    protected array $authenticated;

    /**
     * @var array <string, mixed>
     */
    protected array $meta = [];

    public function __construct()
    {
        $this->setAuthenticated();
    }

    public function setFromWalletId(int|string $fromWalletId): self
    {
        $this->fromWalletId = $fromWalletId;

        return $this;
    }

    public function setToWalletId(int|string $toWalletId): self
    {
        $this->toWalletId = $toWalletId;

        return $this;
    }

    private function setAuthenticated(): self
    {
        $this->authenticated = [
            'id' => auth()->check() ? auth()->id() : null,
            'class' => auth()->check() ? auth()->user() : null,
        ];

        return $this;
    }

    public function setMeta(string $key, mixed $value): self
    {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (array_key_exists($key, $this->meta)) {
            throw new MetaKeyExists("Meta key $key already exists.");
        }

        $this->meta[$key] = $value;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function setMetas(array $meta): self
    {
        foreach ($meta as $key => $value) {
            $this->setMeta($key, $value);
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'from_wallet_id' => $this->fromWalletId,
            'to_wallet_id' => $this->toWalletId,
            'authenticated' => $this->authenticated,
            ...$this->meta,
        ];
    }
}
