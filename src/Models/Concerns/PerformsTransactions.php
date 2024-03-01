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
     * @param  array<string|mixed>|WalletMeta  $meta
     *
     * @throws BindingResolutionException
     */
    protected function performTransaction(
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
            TransactionType::Deposit => $this->depositAction($amount, $meta),
            TransactionType::Withdrawal => $this->withdrawalAction($amount, $meta),
            TransactionType::Transfer => $this->transferAction($toWallet, $amount, $meta, $converter),
        };

        event(new TransactionCompletedEvent($type, $amount, $meta, $toWallet, $converter));
    }

    protected function depositAction(float $amount, WalletMeta $meta, ?WalletModel $toWallet = null, ?CurrencyConverter $converter = null): int|string
    {
        if ($toWallet !== null && $converter !== null) {
            /** @phpstan-ignore-next-line */
            $meta->setConversionMeta($this->currency, $toWallet->currency, $converter);
            /** @phpstan-ignore-next-line */
            $amount = $converter->convert($amount, $this->currency, $toWallet->currency);
        }

        return $this->recordTransaction(TransactionType::Deposit, $amount, $meta, $toWallet);
    }

    protected function withdrawalAction(float $amount, WalletMeta $meta): int|string
    {
        return $this->recordTransaction(TransactionType::Withdrawal, $amount, $meta);
    }

    protected function transferAction(?WalletModel $toWallet, float $amount, WalletMeta $meta, ?CurrencyConverter $converter = null): void
    {
        DB::transaction(function () use ($toWallet, $amount, $meta, $converter): void {
            /** @phpstan-ignore-next-line */
            $this->withdrawalAction($amount, $meta->setToWalletId($toWallet->getAttribute('id')));
            /** @phpstan-ignore-next-line */
            $toWallet->depositAction($amount, $meta->setFromWalletId($this->getAttribute('id')), $toWallet, $converter);
        });
    }

    /**
     * @param  array<string|mixed>|WalletMeta  $meta
     *
     * @throws BindingResolutionException
     */
    protected function prepareMetaObject(array|WalletMeta $meta): WalletMeta
    {
        return $meta instanceof WalletMeta ? $meta : app()->make(WalletMeta::class)->setMetas($meta);
    }
}
