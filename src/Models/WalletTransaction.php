<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int|string $id
 * @property int|string $wallet_id
 * @property float $amount
 * @property string $type
 * @property string $description
 * @property array $meta
 * @property Wallet $wallet
 */
class WalletTransaction extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'wallet_id',
        'amount',
        'type',
        'currency',
        'description',
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

    public static function boot(): void
    {
        parent::boot();

        static::updating(function (Model $transaction) {
            throw new \RuntimeException('Cannot update wallet transaction.');
        });

        static::deleting(function (Model $transaction): never {
            throw new \RuntimeException('Cannot delete wallet transaction.');
        });
    }
}
