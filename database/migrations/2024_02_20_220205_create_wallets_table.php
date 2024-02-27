<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('wallet.table_names.wallets', 'wallets'), function (Blueprint $table) {
            config('wallet.settings.use_uuids', false)
                ? $table->uuid('id')->primary()
                : $table->id();

            $table->string('name')->nullable();
            $table->string('currency');
            $table->morphs('ownable');

            $table->timestamp('created_at')->useCurrent();

            if (config('wallet.settings.one_wallet_per_currency', false)) {
                $table->unique(['ownable_type', 'ownable_id', 'currency']);
            }

            $table->index('currency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('wallet.table_names.wallets', 'wallets'));
    }
};
