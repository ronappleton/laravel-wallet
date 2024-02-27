<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models;

use Appleton\LaravelWallet\Casts\IdColumnCast;
use Appleton\LaravelWallet\Contracts\WalletModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Exceptions\InvalidDeletion;
use Appleton\LaravelWallet\Exceptions\InvalidUpdate;
use Appleton\LaravelWallet\Models\Concerns\PerformsTransactions;
use Appleton\LaravelWallet\Models\Concerns\RecordsTransactions;
use Appleton\LaravelWallet\Models\Concerns\ValidatesTransactions;
use Carbon\Carbon;
use Database\Factories\WalletFactory;
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
        return $this->transactions()->where(function (Builder $query) {
            $query->transactionType(TransactionType::Deposit);
        });
    }

    public function withdrawals(): HasMany
    {
        return $this->transactions()->where(function (Builder $query) {
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

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (self $wallet): void {
            if (config('wallet.settings.use_uuids', false) === true) {
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
