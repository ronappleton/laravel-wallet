<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models\Concerns;

use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Exceptions\InvalidModel;
use Appleton\LaravelWallet\Models\WalletTransaction;

trait RecordsTransactions
{
    protected function recordTransaction(TransactionType $type, float $amount, WalletMeta $meta, ?WalletModel $toWallet = null): int|string
    {
        return $this->getWalletTransactionModel()::create([
            'wallet_id' => $toWallet?->id ?? $this->id,
            'amount' => $amount,
            'type' => $type->value,
            'currency' => $this->currency,
            'meta' => $meta->toArray(),
        ])->id;
    }

    protected function getWalletTransactionModel(): string
    {
        $model = config('wallet.models.transaction.model', WalletTransaction::class);

        if (!is_string($model)) {
            throw new InvalidModel('The wallet transaction model must be a string');
        }

        return $model;
    }
}
