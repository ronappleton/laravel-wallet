<?php

declare(strict_types=1);

namespace Tests\Unit\Events;

use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Events\TransactionStartEvent;
use Faker\Generator;
use Tests\TestCase;

class TransactionStartTest extends TestCase
{
    private readonly TransactionStartEvent $event;

    public function setUp(): void
    {
        parent::setUp();

        $this->faker = new Generator;

        $this->event = new TransactionStartEvent(
            TransactionType::Deposit,
            $this->faker->randomFloat(2),
            $this->mock(WalletMeta::class),
            $this->mock(WalletModel::class),
            $this->mock(CurrencyConverter::class),
        );
    }

    public function testGetType(): void
    {
        $this->assertEquals($this->event->getType(), $this->event->getType());
    }

    public function testGetAmount(): void
    {
        $this->assertEquals($this->event->getAmount(), $this->event->getAmount());
    }

    public function testGetMeta(): void
    {
        $this->assertEquals($this->event->getMeta(), $this->event->getMeta());
    }

    public function testGetToWallet(): void
    {
        $this->assertEquals($this->event->getToWallet(), $this->event->getToWallet());
    }

    public function testGetConverter(): void
    {
        $this->assertEquals($this->event->getConverter(), $this->event->getConverter());
    }
}
