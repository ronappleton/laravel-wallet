<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appleton\LaravelWallet\Contracts\WalletModel as WalletModelContract;
use Appleton\LaravelWallet\Models\Wallet as WalletModel;
use Tests\TestCase;

class WalletModelTest extends TestCase
{
    public function testWalletModelIsBound(): void
    {
        $this->assertInstanceOf(
            WalletModelContract::class,
            $this->app->make(WalletModel::class)
        );
    }
}
