<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet;

use Appleton\LaravelWallet\Contracts\CurrencyService as CurrencyContract;
use Appleton\LaravelWallet\Contracts\WalletMeta as WalletMetaContract;
use Appleton\LaravelWallet\Contracts\WalletModel as WalletModelContract;
use Appleton\LaravelWallet\Contracts\WalletService as WalletContract;
use Appleton\LaravelWallet\Helpers\WalletMeta;
use Appleton\LaravelWallet\Models\Wallet as WalletModel;
use Appleton\LaravelWallet\Services\Currency;
use Appleton\LaravelWallet\Services\Wallet;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            WalletContract::class,
            Wallet::class
        );

        $this->app->bind(
            CurrencyContract::class,
            Currency::class,
        );

        $this->app->bind(
            WalletMetaContract::class,
            WalletMeta::class,
        );

        $walletModel = (string) config('wallet.models.wallet.model', WalletModel::class);

        $this->app->bind(
            WalletModelContract::class,
            $walletModel,
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/wallet.php' => config_path('wallet.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
