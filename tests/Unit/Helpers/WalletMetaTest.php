<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use Appleton\LaravelWallet\Contracts\WalletMeta as WalletMetaContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
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

    /**
     * @throws BindingResolutionException
     */
    public function testToArrayWhenAuthenticated(): void
    {
        $this->actingAs($this->createUser());

        $meta = app()->make(WalletMetaContract::class);
        $this->assertIsArray($meta->toArray());
    }

    private function createUser(): AuthenticatableContract
    {
        Schema::create('users', function ($table) {
            $table->id();
        });

        $user = new class extends Model implements AuthenticatableContract
        {
            use Authenticatable;

            protected $table = 'users';

            public $timestamps = false;
        };

        $user->save();

        return $user;
    }
}
