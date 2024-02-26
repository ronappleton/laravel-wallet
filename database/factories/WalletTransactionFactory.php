<?php

declare(strict_types=1);

namespace Database\Factories;

use Appleton\LaravelWallet\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        return [
            'amount' => $this->faker->randomFloat(),
            'type' => $this->faker->word(),
            'description' => $this->faker->text(),
            'meta' => $this->faker->words(),
        ];
    }
}
