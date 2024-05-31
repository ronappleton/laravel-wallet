<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet;

use Appleton\LaravelWallet\Contracts\WalletMeta as WalletMetaContract;
use Appleton\LaravelWallet\Contracts\WalletModel as WalletModelContract;
use Appleton\LaravelWallet\Contracts\WalletTransactionModel as WalletTransactionModelContract;
use Appleton\LaravelWallet\Helpers\WalletMeta;
use Appleton\LaravelWallet\Models\Wallet as WalletModel;
use Appleton\LaravelWallet\Models\WalletTransaction;
use Appleton\TypedConfig\Facades\TypedConfig as Config;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $walletModel = Config::classString('wallet.wallet_model', WalletModel::class);

        // Wallet Model Binding
        $this->app->bind(WalletModelContract::class, $walletModel);

        $walletTransactionModel =
            Config::classString('wallet.wallet_transaction_model', WalletTransaction::class);

        // Wallet Transaction Model Binding
        $this->app->bind(WalletTransactionModelContract::class, $walletTransactionModel);

        // WalletMeta Binding
        $this->app->bind(WalletMetaContract::class, WalletMeta::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/wallet.php' => config_path('wallet.php'),
        ]);

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
    }
}
