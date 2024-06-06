<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models;

use Appleton\LaravelWallet\Contracts\WalletTransactionModel;
use Appleton\LaravelWallet\Enums\TransactionType;
use Appleton\LaravelWallet\Exceptions\InvalidDeletion;
use Appleton\LaravelWallet\Exceptions\InvalidUpdate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int|string $id
 * @property int|string $wallet_id
 * @property float $amount
 * @property string $type
 * @property array $meta
 * @property Wallet $wallet
 *
 * @method static WalletTransaction transactionType(TransactionType $type)
 */
class WalletTransaction extends Model implements WalletTransactionModel
{
    use HasUuids;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'currency',
        'meta',
    ];

    /**
     * @var array<string, string|class-string>
     */
    protected $casts = [
        'amount' => 'float',
        'meta' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function scopeTransactionType(Builder $query, TransactionType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public static function boot(): void
    {
        parent::boot();

        static::updating(function (Model $transaction) {
            throw new InvalidUpdate('Cannot update wallet transaction.');
        });

        static::deleting(function (Model $transaction): never {
            throw new InvalidDeletion('Cannot delete wallet transaction.');
        });
    }
}
