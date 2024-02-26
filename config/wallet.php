<?php

declare(strict_types=1);

return [
    'models' => [
        'currency' => [
            'model' => \Appleton\LaravelWallet\Enums\Currency::class,
            'currency_attribute' => null,
        ],

        'transaction' => [
            'model' => \Appleton\LaravelWallet\Models\WalletTransaction::class,
        ],

        'wallet' => [
            'model' => \Appleton\LaravelWallet\Models\Wallet::class,
        ],
    ],

    'settings' => [
        'use_uuids' => false,
        'allow_negative_balances' => false,
        'one_wallet_per_currency' => true,
    ],

    'table_names' => [
        'wallets' => 'wallets',
        'wallet_transactions' => 'wallet_transactions',
    ],
];
