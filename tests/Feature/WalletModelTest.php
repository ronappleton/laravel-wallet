<?php

declare(strict_types=1);

namespace Tests\Feature;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Exceptions\CurrencyMisMatch;
use Appleton\LaravelWallet\Exceptions\InsufficientFunds;
use Appleton\LaravelWallet\Exceptions\InvalidDeletion;
use Appleton\LaravelWallet\Exceptions\InvalidUpdate;
use Appleton\LaravelWallet\Exceptions\UnsupportedCurrencyConversion;
use Appleton\LaravelWallet\Exceptions\WalletCreationRetriction;
use Appleton\LaravelWallet\Models\Wallet;
use Appleton\LaravelWallet\Models\Wallet as WalletModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use TypeError;

class WalletModelTest extends TestCase
{
    /**
     * @throws BindingResolutionException
     */
    public function testDeposit(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $wallet->deposit(500);

        $this->assertEquals(500, $wallet->balance());

        $transactions = $wallet->transactions()->get();

        $this->assertCount(1, $transactions);
        $this->assertEquals(500, $transactions->first()->amount);
        $this->assertEquals(TransactionType::Deposit->value, $transactions->first()->type);
        $this->assertEquals('USD', $transactions->first()->currency);
        $this->assertEquals($wallet->getAttribute('id'), $transactions->first()->wallet_id);

        $meta = $transactions->first()->meta;

        $this->assertArrayHasKey('authenticated', $meta);
        $this->assertEquals('Not Authenticated', $meta['authenticated']);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testWithdrawInsufficientFunds(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $this->expectException(InsufficientFunds::class);

        $wallet->withdrawal(300);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testWithdraw(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        config(['wallet.allow_negative_balances' => true]);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $wallet->withdrawal(300);

        $this->assertEquals(-300.0, $wallet->balance());

        $transactions = $wallet->transactions()->get();

        $this->assertCount(1, $transactions);
        $this->assertEquals(300.0, $transactions->first()->amount);
        $this->assertEquals(TransactionType::Withdrawal->value, $transactions->first()->type);
        $this->assertEquals('USD', $transactions->first()->currency);
        $this->assertEquals($wallet->getAttribute('id'), $transactions->first()->wallet_id);

        $meta = $transactions->first()->meta;

        $this->assertArrayHasKey('authenticated', $meta);
        $this->assertEquals('Not Authenticated', $meta['authenticated']);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testTransactions(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $wallet->deposit(500);
        $wallet->withdrawal(300);

        $transactions = $wallet->transactions()->get();

        $this->assertCount(2, $transactions);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testDeposits()
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $wallet->deposit(100);
        $wallet->deposit(200);
        $wallet->deposit(300);
        $wallet->withdrawal(300);

        $deposits = $wallet->deposits()->get();

        $this->assertCount(3, $deposits);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testWithdrawals()
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $wallet->deposit(1000);
        $wallet->withdrawal(300);
        $wallet->withdrawal(200);
        $wallet->withdrawal(100);
        $wallet->withdrawal(200);

        $withdrawals = $wallet->withdrawals()->get();

        $this->assertCount(4, $withdrawals);
    }

    public function testGetOwner(): void
    {
        Schema::create('owners', function ($table) {
            $table->id();
            $table->string('name');
        });

        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);
        $owner->setAttribute('name', 'John Doe');
        $owner->save();

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $this->assertEquals($owner->getAttribute('id'), $wallet->owner->getAttribute('id'));
    }

    /**
     * @throws BindingResolutionException
     */
    public function testTransfer(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $fromWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $toWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->skip(1)
            ->first();

        $fromWallet->deposit(500);
        $fromWallet->transfer($toWallet, 300);

        $this->assertEquals(200, $fromWallet->balance());
        $this->assertEquals(300, $toWallet->balance());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testTransferOfDifferentCurrenciesWithoutConverterFails(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'EUR',
        ]);

        $fromWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $toWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->skip(1)
            ->first();

        $fromWallet->deposit(500);

        $this->expectException(CurrencyMisMatch::class);

        $fromWallet->transfer($toWallet, 300);
    }

    /**
     * @throws BindingResolutionException
     */
    public function testTransferWithConverter(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'EUR',
        ]);

        $fromWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $toWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->skip(1)
            ->first();

        $fromWallet->deposit(500);

        $fromWallet->transfer($toWallet, 300, [], $this->createFakeConverter());

        $this->assertEquals(200, $fromWallet->balance());
        $this->assertEquals(600, $toWallet->balance());
    }

    /**
     * @throws BindingResolutionException
     */
    public function testTransferWithConverterFails(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'EUR',
        ]);

        $fromWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $toWallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->skip(1)
            ->first();

        $fromWallet->deposit(500);

        $this->expectException(UnsupportedCurrencyConversion::class);

        $fromWallet->transfer($toWallet, 300, [], $this->createFakeConverter(['USD', 'GBP']));
    }

    public function testWalletUsingUuids(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $this->assertIsString(Wallet::first()->id);
    }

    public function testUpdatingWalletFails(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $this->expectException(InvalidUpdate::class);

        $wallet->update(['currency' => 'GBP']);
    }

    public function testDeletingWalletFails(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $this->expectException(InvalidDeletion::class);

        $wallet->delete();
    }

    public function testGetWallets(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $this->assertCount(1, $owner->wallets()->get());
    }

    public function testGetWalletsByCurrency(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);

        WalletModel::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'EUR',
        ]);

        $wallet = WalletModel::where('ownable_id', $owner->getAttribute('id'))
            ->where('ownable_type', $owner::class)
            ->first();

        $this->assertCount(1, $owner->wallets()->whereCurrency('USD')->get());
    }

    public function testCreateWallet(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        $wallet = $owner->createWallet('USD');

        $this->assertEquals('USD', $wallet->currency);
        $this->assertEquals($owner->getAttribute('id'), $wallet->ownable_id);
        $this->assertEquals($owner::class, $wallet->ownable_type);
    }

    public function testTransferWithNullToWallet(): void
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        $wallet = $owner->createWallet('USD');

        $this->expectException(TypeError::class);

        $wallet->transfer(null, 100.00, []);
    }

    public function testMultipleWalletsOfSameCurrencyPrevented(): void
    {
        config(['wallet.one_wallet_per_currency' => true]);

        Artisan::call('migrate:fresh');

        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        $owner->createWallet('USD');

        $this->expectException(WalletCreationRetriction::class);

        $owner->createWallet('USD');
    }

    private function createFakeConverter(array $supportedCurrencies = ['USD', 'EUR']): CurrencyConverter
    {
        return new class($supportedCurrencies) implements CurrencyConverter
        {
            public function __construct(private readonly array $supportedCurrencies)
            {
            }

            public function convert(float $amount, string $from, string $to): float
            {
                return $amount * $this->getRate($from, $to);
            }

            public function isSupported(string $from, string $to): bool
            {
                foreach ([$from, $to] as $currency) {
                    if (! in_array($currency, $this->getSupportedCurrencies())) {
                        return false;
                    }
                }

                return true;
            }

            public function getRate(string $from, string $to): float
            {
                return 2.0;
            }

            public function getSupportedCurrencies(): array
            {
                return $this->supportedCurrencies;
            }
        };
    }
}
