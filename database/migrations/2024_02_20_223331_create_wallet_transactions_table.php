<?php

declare(strict_types=1);

use Appleton\TypedConfig\Facades\TypedConfig as Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            Config::string('wallet.wallet_transaction_table_name', 'wallet_transactions'),
            function (Blueprint $table) {
                Config::bool('wallet.use_uuids', false)
                    ? $table->uuid('id')
                    : $table->id();

                $table->string('currency');
                $table->enum('type', ['deposit', 'withdrawal']);

                $table->decimal('amount',
                    18,
                    10,
                    Config::bool('wallet.allow_negative_balances', false)
                );

                $table->json('meta')->nullable();

                $table->timestamps();

                Config::bool('wallet.use_uuids', false)
                    ? $table->foreignUuid('wallet_id')->references('id')->on('wallets')
                    : $table->foreign('wallet_id')->references('id')->on('wallets');

                $table->index('type');
                $table->index(['wallet_id', 'amount']);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::string('wallet.wallet_transaction_table_name', 'wallet_transactions'));
    }
};
