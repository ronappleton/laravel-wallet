<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | These models define those in use by the wallet system.
    | The Wallet and WalletTransaction models are required, the Currency model
    | is optional.
    |
    | If using differing wallet or wallet transaction models, you must implement
    | the required Contract for the models, WalletModel, WalletTransactionModel.
    |
    */
    'models' => [
        /*
        |--------------------------------------------------------------------------
        | Currency Model
        |--------------------------------------------------------------------------
        |
        | The currency model can be either a model class or a BackedEnum class.
        | If using a model class, the currency_attribute is the attribute on the model.
        |
        */
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

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | These settings define the behaviour of the wallet system.
    |
    | use_uuids: If true, the wallet and wallet transaction models will use UUIDs.
    | allow_negative_balances: If true, wallets can have negative balances.
    | one_wallet_per_currency: If true, a user can only have one wallet per currency.
    |
    */
    'settings' => [
        'use_uuids' => false,
        'allow_negative_balances' => false,
        'one_wallet_per_currency' => true,
    ],

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
    'table_names' => [
        'wallets' => 'wallets',
        'wallet_transactions' => 'wallet_transactions',
    ],
];
