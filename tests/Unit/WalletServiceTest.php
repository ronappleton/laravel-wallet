<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appleton\LaravelWallet\Contracts\WalletService as WalletContract;
use Appleton\LaravelWallet\Services\Wallet;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    public function testServiceIsBound(): void
    {
        $this->assertInstanceOf(
            WalletContract::class,
            $this->app->make(WalletContract::class)
        );
    }

    public function testHasWalletsTraitNotImplemented(): void
    {
        $owner = new class extends Model
        {
        };

        $service = app(Wallet::class);

        $this->expectException(RuntimeException::class);

        $service->getWallet($owner);
    }
}
