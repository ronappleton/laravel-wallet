<?php

declare(strict_types=1);

namespace Tests\Unit;

use Appleton\LaravelWallet\Contracts\CurrencyService as CurrencyContract;
use Appleton\LaravelWallet\Enums\Currency as CurrencyEnum;
use Appleton\LaravelWallet\Services\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\MockObject\Exception;
use RuntimeException;
use Tests\Feature\Models\CurrencyModel;
use Tests\Feature\Models\FakeCurrencyModel;
use Tests\TestCase;

class CurrencyServiceTest extends TestCase
{
    public function testServiceIsBound(): void
    {
        $this->assertInstanceOf(
            CurrencyContract::class,
            $this->app->make(CurrencyContract::class)
        );
    }

    public function testGetCurrencyWithString(): void
    {
        $service = new Currency();

        $this->assertEquals('USD', $service->getCurrency('USD'));
    }

    public function testGetCurrencyWithEnum(): void
    {
        $service = new Currency();

        $this->assertEquals('USD', $service->getCurrency(CurrencyEnum::USD));
    }

    public function testGetCurrencyWithModel(): void
    {
        $currency = new class extends Model
        {
        };

        $currency->setAttribute('currency', 'USD');

        $service = new Currency();

        $this->assertEquals('USD', $service->getCurrency($currency));
    }

    /**
     * @throws Exception
     */
    public function testGetCurrencyFromIntegerId(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_name')->nullable();
        });

        CurrencyModel::create([
            'id' => 1,
            'currency_name' => 'USD',
        ]);

        config(['wallet.models.currency.model' => CurrencyModel::class]);
        config(['wallet.models.currency.currency_attribute' => 'currency_name']);

        $service = new Currency();

        $this->assertEquals('USD', $service->getCurrency(1));
    }

    public function testGetCurrencyByIdWithConfigNotSet(): void
    {
        $service = new Currency();

        $this->expectException(RuntimeException::class);

        $service->getCurrency(1);
    }

    public function testGetCurrencyByIdWhenModelNotValid(): void
    {
        $service = new Currency();

        $this->expectException(RuntimeException::class);

        config(['wallet.models.currency.model' => FakeCurrencyModel::class]);

        $service->getCurrency(1);
    }

    public function testGetCurrencyByIdWhenAttributeNotSet(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_name')->nullable();
        });

        CurrencyModel::create([
            'id' => 1,
            'currency_name' => 'USD',
        ]);

        $service = new Currency();

        $this->expectException(RuntimeException::class);

        config(['wallet.models.currency.model' => CurrencyModel::class]);

        $service->getCurrency(1);
    }
}
