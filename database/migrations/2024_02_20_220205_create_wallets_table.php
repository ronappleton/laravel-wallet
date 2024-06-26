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
        Schema::create(Config::string('wallet.wallet_table_name', 'wallets'), function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name')->nullable();
            $table->string('currency');

            $table->uuidMorphs('ownable');

            $table->timestamp('created_at')->useCurrent();

            if (Config::bool('wallet.one_wallet_per_currency', false)) {
                $table->unique(['ownable_type', 'ownable_id', 'currency']);
            }

            $table->index('currency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::string('wallet.wallet_table_name', 'wallets'));
    }
};
