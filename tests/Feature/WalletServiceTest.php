<?php

declare(strict_types=1);

namespace Tests\Feature;

use Appleton\LaravelWallet\Models\Wallet as WalletModel;
use Appleton\LaravelWallet\Services\Wallet;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'sqlite'])->run();

        Schema::create('owners', function (Blueprint $table) {
            $table->id();
        });
    }

    public function testGetWallets(): void
    {
        $owner = $this->createOwner();

        $ownerInstance = $owner->newQuery()->create();

        WalletModel::factory(2)->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
        ]);

        $service = app(Wallet::class);

        $wallets = $service->getWallets($ownerInstance);
        $this->assertCount(2, $wallets);
    }

    public function testGetWalletsWithCurrency(): void
    {
        $owner = $this->createOwner();

        $ownerInstance = $owner->newQuery()->create();

        $wallets = WalletModel::factory(2)->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
            'currency' => 'USD',
        ]);

        $service = app(Wallet::class);

        $wallets = $service->getWallets($ownerInstance, 'USD');

        $this->assertCount(2, $wallets);
    }

    public function testGetWalletsWithName(): void
    {
        $owner = $this->createOwner();

        $ownerInstance = $owner->newQuery()->create();

        WalletModel::factory()->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
            'name' => 'Wallet 1',
        ]);

        WalletModel::factory()->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
        ]);

        $service = app(Wallet::class);

        $wallets = $service->getWallets($ownerInstance, null, 'Wallet 1');

        $this->assertCount(1, $wallets);
    }

    public function testGetWalletsWithCurrencyAndName(): void
    {
        $owner = $this->createOwner();

        $ownerInstance = $owner->newQuery()->create();

        WalletModel::factory()->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
            'name' => 'Wallet 1',
            'currency' => 'USD',
        ]);

        WalletModel::factory()->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
            'name' => 'Wallet 2',
        ]);

        $service = app(Wallet::class);

        $wallets = $service->getWallets($ownerInstance, 'USD', 'Wallet 1');

        $this->assertCount(1, $wallets);
    }

    public function testGetWallet(): void
    {
        $owner = $this->createOwner();

        $ownerInstance = $owner->newQuery()->create();

        WalletModel::factory()->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
        ]);

        $service = app(Wallet::class);

        $wallet = $service->getWallet($ownerInstance);

        $this->assertInstanceOf(WalletModel::class, $wallet);
    }

    public function testGetWalletWithMultipleResults(): void
    {
        $owner = $this->createOwner();

        $ownerInstance = $owner->newQuery()->create();

        WalletModel::factory(2)->create([
            'ownable_id' => $ownerInstance->id,
            'ownable_type' => get_class($ownerInstance),
        ]);

        $service = app(Wallet::class);

        $this->expectException(RuntimeException::class);

        $service->getWallet($ownerInstance);
    }

    public function testUuidIsSetWhenUsingUuids(): void
    {
        config(['wallet.settings.use_uuids' => true]);

        $this->artisan('migrate:fresh', ['--database' => 'sqlite'])->run();

        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
        ]);

        $wallet = WalletModel::first();

        $this->assertTrue(Str::isUuid($wallet->getAttribute('id')));
    }

    public function testUpdatingPrevented(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        $wallet = WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
        ]);

        $this->expectException(RuntimeException::class);

        $wallet->update(['name' => 'Updated']);
    }

    public function testDeletingPrevented(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        $wallet = WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
        ]);

        $this->expectException(RuntimeException::class);

        $wallet->delete();
    }
}
