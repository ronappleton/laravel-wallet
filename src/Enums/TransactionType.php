<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Transfer = 'transfer';
}
