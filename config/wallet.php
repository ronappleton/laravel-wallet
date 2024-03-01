<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | These models define those in use by the wallet system.
    |
    | If using differing wallet or wallet transaction models, you must implement
    | the required Contract for the models, WalletModel, WalletTransactionModel.
    |
    */
    'wallet_model' => \Appleton\LaravelWallet\Models\Wallet::class,
    'wallet_transaction_model' => \Appleton\LaravelWallet\Models\WalletTransaction::class,

    /*
    |--------------------------------------------------------------------------
    | Use Uuids
    |--------------------------------------------------------------------------
    |
    | If true, the wallet and wallet transaction models will use UUIDs.
    */
    'use_uuids' => false,

    /*
    |--------------------------------------------------------------------------
    | Allow Negative Balances
    |--------------------------------------------------------------------------
    |
    | Whether a wallet balance can be negative.
    */
    'allow_negative_balances' => false,

    /*
    |--------------------------------------------------------------------------
    | One Wallet Per Currency
    |--------------------------------------------------------------------------
    |
    | If true, only one wallet can be created per currency per owner.
    */
    'one_wallet_per_currency' => true,

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | This simply sets the table names for the wallet and wallet transaction
    | models.
    |
    | Useful if you are integrating into an existing platform.
    |
    */
    'wallet_table_name' => 'wallets',
    'wallet_transaction_table_name' => 'wallet_transactions',
];
