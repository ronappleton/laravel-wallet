<?php

declare(strict_types=1);

namespace Database\Factories;

use Appleton\LaravelWallet\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'currency' => $this->faker->word(),
            'created_at' => Carbon::now(),
        ];
    }
}
