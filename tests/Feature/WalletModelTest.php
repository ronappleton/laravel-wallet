<?php

declare(strict_types=1);

namespace Tests\Feature;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Models\Wallet as WalletModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use ReflectionException;
use RuntimeException;
use Tests\TestCase;

class WalletModelTest extends TestCase
{
    use DatabaseMigrations;

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
    }

    public function testWithdraw(): void
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
        $wallet->withdraw(300);

        $this->assertEquals(200, $wallet->balance());
    }

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
        $wallet->withdraw(300);

        $transactions = $wallet->transactions()->get();

        $this->assertCount(2, $transactions);
    }

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
        $wallet->withdraw(300);

        $deposits = $wallet->deposits();

        $this->assertCount(3, $deposits);
    }

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
        $wallet->withdraw(300);
        $wallet->withdraw(200);
        $wallet->withdraw(100);
        $wallet->withdraw(200);

        $withdrawals = $wallet->withdrawals();

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
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    public function testInvalidTransactType(): void
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

        $this->expectException(RuntimeException::class);

        $reflection = new \ReflectionClass(get_class($wallet));
        $method = $reflection->getMethod('createTransaction');
        $method->invokeArgs($wallet, ['invalid', 500, null, app()->make(WalletMeta::class)]);
    }

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

        $this->expectException(RuntimeException::class);

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

        $this->expectException(RuntimeException::class);

        $fromWallet->transfer($toWallet, 300, [], $this->createFakeConverter(['USD', 'GBP']));
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
