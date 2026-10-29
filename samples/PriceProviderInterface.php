<?php

namespace App\Domain\PriceProvider\Contract;

use App\Domain\PriceProvider\ValueObjects\PriceQuote;

/**
 * Every external price source implements this.
 *
 * Adding a new source:
 *   1) a class implementing this interface
 *   2) one line in the resolver (key => class)
 *   3) one row in the providers table
 *
 * Rules:
 *   - prices are per one unit of our asset
 *   - closed market or a bad response => throw; never return a zero or partial price
 */
interface PriceProviderInterface
{
    /**
     * Prices this source offers: key => [label, our asset symbol]
     *
     * @return array<string, array{label:string, asset:string}>
     */
    public function items(): array;

    public function getPrice(?string $item = null): PriceQuote;
}
