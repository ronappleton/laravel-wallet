<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models;

use Appleton\LaravelWallet\Casts\IdColumnCast;
use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletMeta;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Events\ConversionCompletedEvent;
use Appleton\LaravelWallet\Events\ConversionStartedEvent;
use Appleton\LaravelWallet\Events\DepositCompletedEvent;
use Appleton\LaravelWallet\Events\DepositStartedEvent;
use Appleton\LaravelWallet\Events\TransferCompletedEvent;
use Appleton\LaravelWallet\Events\TransferStartedEvent;
use Appleton\LaravelWallet\Events\WithdrawalCompletedEvent;
use Appleton\LaravelWallet\Events\WithdrawalStartedEvent;
use Carbon\Carbon;
use Database\Factories\WalletFactory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @property-read int|string $id
 * @property string $name
 * @property string $currency
 * @property string $ownable_type
 * @property string $ownable_id
 * @property float $balance
 * @property Carbon $created_at
 * @property Model $owner
 * @property Collection $deposits
 * @property Collection $withdrawals
 * @property Collection $transactions
 */
class Wallet extends Model implements WalletModel
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'currency',
        'owner_type',
        'owner_id',
    ];

    protected $casts = [
        'id' => IdColumnCast::class,
        'meta' => 'array',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo('ownable');
    }

    /**
     * @param  array<string, mixed>|WalletMeta  $meta
     *
     * @throws BindingResolutionException
     */
    public function deposit(float $amount, string $description = '', array|WalletMeta $meta = []): int
    {
        event(new DepositStartedEvent($this, $amount, $description, $meta));

        $metaObject = $meta instanceof WalletMeta ? $meta : app()->make(WalletMeta::class)->setMetas($meta);

        $transactionId = $this->createTransaction('deposit', $amount, $description, $metaObject);

        event(new DepositCompletedEvent($this, $amount, $transactionId, $description, $metaObject));

        return $transactionId;
    }

    /**
     * @param  array<string, mixed>|WalletMeta  $meta
     *
     * @throws BindingResolutionException
     */
    public function withdraw(float $amount, string $description = '', array|WalletMeta $meta = []): int
    {
        event(new WithdrawalStartedEvent($this, $amount, $description, $meta));

        $metaObject = $meta instanceof WalletMeta ? $meta : app()->make(WalletMeta::class)->setMetas($meta);

        $transactionId = $this->createTransaction('withdraw', $amount, $description, $metaObject);

        event(new WithdrawalCompletedEvent($this, $amount, $transactionId, $description, $metaObject));

        return $transactionId;
    }

    /**
     * @param  array<string, mixed>  $meta
     *
     * @throws BindingResolutionException
     */
    public function transfer(
        WalletModel $wallet,
        float $amount,
        array $meta = [],
        ?CurrencyConverter $converter = null
    ): void {
        event(new TransferStartedEvent($this, $wallet, $amount, $meta, $converter));

        /** @var Wallet $wallet */
        if ($this->currency !== $wallet->currency && $converter === null) {
            throw new RuntimeException('Cannot transfer between wallets with different currencies');
        }

        $metaObject = app()->make(WalletMeta::class)->setMetas($meta);

        DB::transaction(function () use ($wallet, $amount, $metaObject, $converter): void {
            $this->withdraw($amount, 'transfer', $metaObject->setToWalletId($wallet->id));

            if ($converter !== null) {
                $amount = $this->handleCurrencyConversion($converter, $amount, $wallet, $metaObject);
            }

            $wallet->deposit($amount, 'transfer', $metaObject->setFromWalletId($this->id));
        });

        event(new TransferCompletedEvent($this, $wallet, $amount, $metaObject, $converter));
    }

    public function deposits(): Collection
    {
        return $this->transactions->where('type', 'deposit');
    }

    public function withdrawals(): Collection
    {
        return $this->transactions->where('type', 'withdraw');
    }

    public function balance(): float
    {
        return $this->deposits()->sum('amount') - $this->withdrawals()->sum('amount');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany($this->getWalletTransactionModel(), 'wallet_id', 'id');
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $wallet): void {
            if (config('wallet.settings.use_uuids', false) === true) {
                $wallet->setAttribute('id', (string) Str::orderedUuid());
            }
        });

        static::updating(function (self $wallet): never {
            throw new RuntimeException('Updating wallets is not supported');
        });

        static::deleting(function (self $wallet): never {
            throw new RuntimeException('Deleting wallets is not supported');
        });
    }

    protected static function newFactory(): WalletFactory
    {
        return WalletFactory::new();
    }

    protected function getWalletTransactionModel(): string
    {
        return config('wallet.models.transaction.model', WalletTransaction::class);
    }

    protected function createTransaction(string $type, float $amount, ?string $description, WalletMeta $meta): int|string
    {
        if (! in_array($type, ['deposit', 'withdraw'])) {
            throw new RuntimeException('Invalid transaction type');
        }

        return $this->getWalletTransactionModel()::create([
            'wallet_id' => $this->id,
            'amount' => $amount,
            'type' => $type,
            'currency' => $this->currency,
            'description' => $description,
            'meta' => $meta->toArray(),
        ])->id;
    }

    private function handleCurrencyConversion(
        CurrencyConverter $converter,
        float $amount,
        WalletModel $wallet,
        WalletMeta $meta
    ): float {
        /** @var Wallet $wallet */
        if (! $converter->isSupported($this->currency, $wallet->currency)) {
            throw new RuntimeException('Currency conversion not supported');
        }

        event(new ConversionStartedEvent($this, $wallet, $amount, $meta, $converter));

        $conversionData = [
            'exchange_rate' => $converter->getRate($this->currency, $wallet->currency),
            'converter' => $converter::class,
            'converted_amount' => $converter->convert($amount, $this->currency, $wallet->currency),
        ];

        $meta->setMeta('conversion', $conversionData);

        event(new ConversionCompletedEvent($this, $wallet, $amount, $meta, $converter));

        return $conversionData['converted_amount'];
    }
}
