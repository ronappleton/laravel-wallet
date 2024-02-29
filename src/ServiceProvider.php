<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet;

use Appleton\LaravelWallet\Contracts\CurrencyService as CurrencyContract;
use Appleton\LaravelWallet\Contracts\WalletMeta as WalletMetaContract;
use Appleton\LaravelWallet\Contracts\WalletModel as WalletModelContract;
use Appleton\LaravelWallet\Contracts\WalletService as WalletContract;
use Appleton\LaravelWallet\Contracts\WalletTransactionModel as WalletTransactionModelContract;
use Appleton\LaravelWallet\Helpers\WalletMeta;
use Appleton\LaravelWallet\Models\Wallet as WalletModel;
use Appleton\LaravelWallet\Models\WalletTransaction;
use Appleton\LaravelWallet\Services\Currency;
use Appleton\LaravelWallet\Services\Wallet;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        /** @var string $walletModel */
        $walletModel = config('wallet.models.wallet.model', WalletModel::class);

        // Wallet Model Binding
        $this->app->bind(WalletModelContract::class, $walletModel,);

        /** @var string $walletTransactionModel */
        $walletTransactionModel =
            config('wallet.models.wallet_transaction.model', WalletTransaction::class);

        // Wallet Transaction Model Binding
        $this->app->bind(WalletTransactionModelContract::class, $walletTransactionModel);

        // Wallet Service Binding
        $this->app->bind(WalletContract::class, Wallet::class);

        // Currency Service Binding
        $this->app->bind(CurrencyContract::class, Currency::class,);

        // WalletMeta Binding
        $this->app->bind(WalletMetaContract::class, WalletMeta::class,);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/wallet.php' => config_path('wallet.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
