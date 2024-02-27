<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models\Concerns;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Events\TransactionCompletedEvent;
use Appleton\LaravelWallet\Events\TransactionStartEvent;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\DB;

trait PerformsTransactions
{
    /**
     * @throws BindingResolutionException
     */
    public function performTransaction(
        TransactionType $type,
        float $amount,
        array|WalletMeta $meta = [],
        ?WalletModel $toWallet = null,
        ?CurrencyConverter $converter = null
    ): void {
        event(new TransactionStartEvent($type, $amount, $meta, $toWallet, $converter));

        $this->validateTransaction($type, $amount, $toWallet, $converter);

        $meta = $this->prepareMetaObject($meta);

        match ($type) {
            TransactionType::Deposit => $this->deposit($amount, $meta),
            TransactionType::Withdrawal => $this->withdrawal($amount, $meta),
            TransactionType::Transfer => $this->transfer($toWallet, $amount, $meta, $converter),
        };

        event(new TransactionCompletedEvent($type, $amount, $meta, $toWallet, $converter));
    }

    protected function deposit(float $amount, WalletMeta $meta, ?WalletModel $toWallet = null, ?CurrencyConverter $converter = null): int
    {
        if ($toWallet !== null && $converter !== null) {
            $meta->setConversionMeta($this->currency, $toWallet->currency, $converter);
            $amount = $converter->convert($amount, $this->currency, $toWallet->currency);
        }

        return $this->recordTransaction(TransactionType::Deposit, $amount, $meta, $toWallet);
    }

    protected function withdrawal(float $amount, WalletMeta $meta): int
    {
        return $this->recordTransaction(TransactionType::Withdrawal, $amount, $meta);
    }

    protected function transfer(WalletModel $toWallet, float $amount, WalletMeta $meta, ?CurrencyConverter $converter = null): void
    {
        DB::transaction(function () use ($toWallet, $amount, $meta, $converter): void {
            $this->withdrawal($amount, $meta->setToWalletId($toWallet->id));
            $toWallet->deposit($amount, $meta->setFromWalletId($this->id), $toWallet, $converter);
        });
    }

    /**
     * @throws BindingResolutionException
     */
    protected function prepareMetaObject(array|WalletMeta $meta): WalletMeta
    {
        return $meta instanceof WalletMeta ? $meta : app()->make(WalletMeta::class)->setMetas($meta);
    }
}
