<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            config('wallet.table_names.wallet_transactions', 'wallet_transactions'),
            function (Blueprint $table) {
                config('wallet.settings.use_uuids', false)
                    ? $table->uuid('id')
                    : $table->id();

                $table->unsignedBigInteger('wallet_id');
                $table->string('currency');
                $table->enum('type', ['deposit', 'withdraw']);

                $table->decimal('amount',
                    18,
                    10,
                    (bool) config('wallet.settings.allow_negative_balances', false)
                );

                $table->string('description')->nullable();
                $table->json('meta')->nullable();

                $table->timestamps();

                $table->foreign('wallet_id')->references('id')->on('wallets');

                $table->index('type');
                $table->index(['wallet_id', 'amount']);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('wallet.table_names.wallet_transactions', 'wallet_transactions'));
    }
};
