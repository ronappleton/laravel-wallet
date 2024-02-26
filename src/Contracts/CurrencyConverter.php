<?php

declare(strict_types=1);

namespace Appleton\LaravelWallet\Contracts;

interface CurrencyConverter
{
    public function convert(float $amount, string $from, string $to): float;

    public function isSupported(string $from, string $to): bool;

    public function getRate(string $from, string $to): float;

    /**
     * @return array<int, string>
     */
    public function getSupportedCurrencies(): array;
}
