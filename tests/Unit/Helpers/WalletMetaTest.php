<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Appleton\LaravelWallet\Contracts\WalletMeta as WalletMetaContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use RuntimeException;
use stdClass;
use Tests\TestCase;

class WalletMetaTest extends TestCase
{
    public function testHelperIsBound(): void
    {
        $this->assertInstanceOf(
            WalletMetaContract::class,
            $this->app->make(WalletMetaContract::class),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function testSetFromWalletId(): void
    {
        $meta = app()->make(WalletMetaContract::class);
        $this->assertInstanceOf(
            WalletMetaContract::class,
            $meta->setFromWalletId(1),
        );
    }

    public function testSetToWalletId(): void
    {
        $meta = app()->make(WalletMetaContract::class);
        $this->assertInstanceOf(
            WalletMetaContract::class,
            $meta->setToWalletId(1),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function testSetMeta(): void
    {
        $meta = app()->make(WalletMetaContract::class);
        $this->assertInstanceOf(
            WalletMetaContract::class,
            $meta->setMeta('key', 'value'),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function testSetSetMetaWithObject(): void
    {
        $meta = app()->make(WalletMetaContract::class);
        $this->assertInstanceOf(
            WalletMetaContract::class,
            $meta->setMeta('key', new stdClass()),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function testSetMetaWhenKeyExists(): void
    {
        $meta = app()->make(WalletMetaContract::class);
        $meta->setMeta('key', 'value');

        $this->expectException(RuntimeException::class);
        $meta->setMeta('key', 'value');
    }

    /**
     * @throws BindingResolutionException
     */
    public function testSetMetas(): void
    {
        $meta = app()->make(WalletMetaContract::class);
        $this->assertInstanceOf(
            WalletMetaContract::class,
            $meta->setMetas(['key' => 'value']),
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function testToArray(): void
    {
        $meta = app()->make(WalletMetaContract::class);
        $this->assertIsArray($meta->toArray());
    }
}
