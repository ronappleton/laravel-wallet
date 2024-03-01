<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Contracts\WalletTransactionModel;
use Tests\TestCase;

class PackageBindingsTest extends TestCase
{
    public function testWalletModelBound(): void
    {
        $this->assertInstanceOf(WalletModel::class, app(WalletModel::class));
    }

    public function testWalletTransactionModelBound(): void
    {
        $this->assertInstanceOf(WalletTransactionModel::class, app(WalletTransactionModel::class));
    }

    public function testWalletMetaBound(): void
    {
        $this->assertInstanceOf(WalletMeta::class, app(WalletMeta::class));
    }
}
