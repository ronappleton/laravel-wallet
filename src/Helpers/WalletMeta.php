<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Helpers;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
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

    private function setAuthenticated(): void
    {
        $this->authenticated = [
            'id' => auth()->check() ? auth()->id() : null,
            'class' => auth()->check() ? auth()->user() : null,
        ];
    }

    public function setConversionMeta(string $from, string $to, CurrencyConverter $converter): self
    {
        $this->setMeta('conversion', [
            'from' => $from,
            'to' => $to,
            'rate' => $converter->getRate($from, $to),
            'converter' => $converter::class,
        ]);

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
        $data = [...$this->meta];

        if ($this->fromWalletId !== null) {
            $data['from_wallet_id'] = $this->fromWalletId;
        }

        if ($this->toWalletId !== null) {
            $data['to_wallet_id'] = $this->toWalletId;
        }

        if ($this->authenticated['id'] !== null) {
            $data['authenticated'] = $this->authenticated;
        } else {
            $data['authenticated'] = 'Not Authenticated';
        }

        return $data;
    }
}
