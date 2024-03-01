<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models\Concerns;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Exceptions\CurrencyMisMatch;
use Appleton\LaravelWallet\Exceptions\InsufficientFunds;
use Appleton\LaravelWallet\Exceptions\UnsupportedCurrencyConversion;
use Appleton\TypedConfig\Facades\TypedConfig as Config;

trait ValidatesTransactions
{
    protected function validateTransaction(
        TransactionType $type,
        float $amount,
        ?WalletModel $toWallet = null,
        ?CurrencyConverter $converter = null
    ): void {
        match ($type) {
            TransactionType::Deposit => $this->validateDeposit($amount),
            TransactionType::Withdrawal => $this->validateWithdrawal($amount),
            TransactionType::Transfer => $this->validateTransfer($toWallet, $converter),
        };
    }

    protected function validateDeposit(float $amount): void
    {
        // ...
    }

    protected function validateWithdrawal(float $amount): void
    {
        $this->checkBalance($amount);
    }

    protected function validateTransfer(?WalletModel $toWallet, ?CurrencyConverter $converter = null): void
    {
        assert(
            $toWallet instanceof WalletModel,
            'Wallet model must be an instance of '.WalletModel::class
        );

        /** @phpstan-ignore-next-line */
        if ($this->currency === $toWallet->currency) {
            return;
        }

        /** @phpstan-ignore-next-line */
        if ($this->currency !== $toWallet->currency && $converter === null) {
            throw new CurrencyMisMatch('Cannot transfer between wallets with different currencies');
        }

        /** @phpstan-ignore-next-line */
        if (! $converter?->isSupported($this->currency, $toWallet->currency)) {
            throw new UnsupportedCurrencyConversion('Currency conversion not supported');
        }
    }

    protected function checkBalance(float $amount): void
    {
        $allowNegativeBalances = Config::bool('wallet.allow_negative_balances', false);

        if (! $allowNegativeBalances && $this->balance() - $amount < 0) {
            throw new InsufficientFunds('Insufficient funds');
        }
    }
}
