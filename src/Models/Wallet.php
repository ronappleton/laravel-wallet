<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models;

use Appleton\LaravelWallet\Casts\IdColumnCast;
use Appleton\LaravelWallet\Contracts\CurrencyConverter;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Exceptions\InvalidDeletion;
use Appleton\LaravelWallet\Exceptions\InvalidUpdate;
use Appleton\LaravelWallet\Models\Concerns\PerformsTransactions;
use Appleton\LaravelWallet\Models\Concerns\RecordsTransactions;
use Appleton\LaravelWallet\Models\Concerns\ValidatesTransactions;
use Appleton\TypedConfig\Facades\TypedConfig as Config;
use BackedEnum;
use Carbon\Carbon;
use Database\Factories\WalletFactory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
 *
 * @method static void deposit(float $amount, array $meta = [])
 * @method static void withdrawal(float $amount, array $meta = [])
 * @method static void transfer(Wallet $wallet, float $amount, array $meta = [], ?CurrencyConverter $converter = null)
 * @method static WalletFactory factory($count = null, $state = [])
 * @method static Builder whereCurrency(string $currency)
 */
class Wallet extends Model implements WalletModel
{
    use HasFactory;
    use PerformsTransactions;
    use RecordsTransactions;
    use ValidatesTransactions;

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

    public function deposits(): HasMany
    {
        return $this->transactions()
            // @phpstan-ignore-next-line
            ->where(function (Builder $query) {
                // @phpstan-ignore-next-line
                $query->transactionType(TransactionType::Deposit);
            });
    }

    public function withdrawals(): HasMany
    {
        return $this->transactions()
            // @phpstan-ignore-next-line
            ->where(function (Builder $query) {
                // @phpstan-ignore-next-line
                $query->transactionType(TransactionType::Withdrawal);
            });
    }

    public function balance(): float
    {
        return $this->deposits()->get()->sum('amount')
            - $this->withdrawals()->get()->sum('amount');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany($this->getWalletTransactionModel());
    }

    public function scopeWhereCurrency(Builder $query, string|BackedEnum $currency): Builder
    {
        return $query->where('currency', $currency instanceof BackedEnum ? $currency->value : $currency);
    }

    /**
     * @param  array<string|mixed>  $meta
     *
     * @throws BindingResolutionException
     */
    public function deposit(float $amount, array $meta = []): void
    {
        $this->performTransaction(TransactionType::Deposit, $amount, $meta);
    }

    /**
     * @param  array<string|mixed>  $meta
     *
     * @throws BindingResolutionException
     */
    public function withdrawal(float $amount, array $meta = []): void
    {
        $this->performTransaction(TransactionType::Withdrawal, $amount, $meta);
    }

    /**
     * @param  array<string|mixed>  $meta
     *
     * @throws BindingResolutionException
     */
    public function transfer(WalletModel $toWallet, float $amount, array $meta = [], ?CurrencyConverter $converter = null): void
    {
        $this->performTransaction(TransactionType::Transfer, $amount, $meta, $toWallet, $converter);
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $wallet): void {
            if (Config::bool('wallet.use_uuids', false) === true) {
                $wallet->setAttribute('id', (string) Str::orderedUuid());
            }
        });

        static::updating(function (self $wallet): never {
            throw new InvalidUpdate('Updating wallets is not supported');
        });

        static::deleting(function (self $wallet): never {
            throw new InvalidDeletion('Deleting wallets is not supported');
        });
    }

    protected static function newFactory(): WalletFactory
    {
        return WalletFactory::new();
    }
}
