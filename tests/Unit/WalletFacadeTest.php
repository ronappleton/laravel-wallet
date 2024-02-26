<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appleton\LaravelWallet\Contracts\WalletService as WalletContract;
use Appleton\LaravelWallet\Facades\Wallet;
use Tests\TestCase;

class WalletFacadeTest extends TestCase
{
    public function testFacadeIsBound(): void
    {
        $this->assertInstanceOf(
            WalletContract::class,
            Wallet::getFacadeRoot()
        );
    }
}
