<?php

namespace App\Domain\Order\Contract;

use App\Domain\Order\Entities\OrderQuoteEntity;
use App\Domain\Order\Enums\OrderType;

/**
 * Domain only knows this interface; the Eloquent class lives in Infrastructure.
 */
interface OrderQuoteRepositoryInterface
{
    public function create(
        int $userId,
        int $assetId,
        OrderType $type,
        int $quantity,
        int $unitPrice,
        string $expiresAt,
    ): OrderQuoteEntity;

    // row lock, used when the quote is turned into an order
    public function findByIdForUpdate(int $id): ?OrderQuoteEntity;

    public function save(OrderQuoteEntity $quote): void;
}
