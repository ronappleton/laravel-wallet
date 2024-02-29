<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models\Concerns;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Exceptions\CurrencyMisMatch;
use Appleton\LaravelWallet\Exceptions\InsufficientFunds;
use Appleton\LaravelWallet\Exceptions\InvalidModel;
use Appleton\LaravelWallet\Exceptions\UnsupportedCurrencyConversion;

trait ValidatesTransactions
{
    public function validateTransaction(
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

    public function validateDeposit(float $amount): void
    {
        // ...
    }

    public function validateWithdrawal(float $amount): void
    {
        $this->checkBalance($amount);
    }

    public function validateTransfer(?WalletModel $toWallet, ?CurrencyConverter $converter = null): void
    {
        if (is_null($toWallet)) {
            throw new InvalidModel('A wallet to transfer to must be provided');
        }

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

    private function checkBalance(float $amount): void
    {
        $allowNegativeBalances = config('wallet.settings.allow_negative_balances', false);

        if (! $allowNegativeBalances && $this->balance() - $amount < 0) {
            throw new InsufficientFunds('Insufficient funds');
        }
    }
}
