<?php

declare(strict_types=1);

namespace Tests\Feature;

use Appleton\LaravelWallet\Models\Wallet;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use RuntimeException;
use Tests\TestCase;

class WalletTransactionsModelTest extends TestCase
{
    use DatabaseMigrations;

    public function testTransactionCannotBeUpdated(): void
    {
        $wallet = $this->createWallet();

        $transaction = $wallet->transactions()->create([
            'amount' => 500,
            'type' => 'deposit',
            'currency' => $wallet->getAttribute('currency'),
        ]);

        $this->expectException(RuntimeException::class);

        $transaction->update(['amount' => 1000]);
    }

    public function testTransactionCannotBeDeleted(): void
    {
        $wallet = $this->createWallet();

        $transaction = $wallet->transactions()->create([
            'amount' => 500,
            'type' => 'deposit',
            'currency' => $wallet->getAttribute('currency'),
        ]);

        $this->expectException(RuntimeException::class);

        $transaction->delete();
    }

    public function testGetWallet()
    {
        $wallet = $this->createWallet();

        $wallet->transactions()->create([
            'amount' => 500,
            'type' => 'deposit',
            'currency' => $wallet->getAttribute('currency'),
        ]);

        $this->assertEquals($wallet->id, $wallet->transactions()->first()->wallet->id);
    }

    private function createWallet()
    {
        $owner = $this->createOwner();
        $owner->setAttribute('id', 1);

        return Wallet::factory()->create([
            'ownable_id' => $owner->getAttribute('id'),
            'ownable_type' => $owner::class,
            'currency' => 'USD',
        ]);
    }
}
