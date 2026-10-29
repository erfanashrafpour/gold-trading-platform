<?php

namespace App\Application\Order\UseCases;

use App\Domain\AssetPrice\Contract\AssetPriceRepositoryInterface;
use App\Domain\CustomerGroup\Contract\CustomerGroupRepositoryInterface;
use App\Domain\Order\Contract\OrderQuoteRepositoryInterface;
use App\Domain\Order\Entities\OrderQuoteEntity;
use App\Domain\Order\Enums\OrderType;
use RuntimeException;

/**
 * Locks the live price for a short time.
 * A buy/sell order can only be placed against a valid quote.
 * (Simplified for the showcase.)
 */
class CreateOrderQuoteAction
{
    private const QUOTE_TTL_SECONDS = 30;

    public function __construct(
        private readonly OrderQuoteRepositoryInterface $quotes,
        private readonly AssetPriceRepositoryInterface $prices,
        private readonly CustomerGroupRepositoryInterface $groups,
    ) {
    }

    public function execute(int $userId, int $assetId, OrderType $type, int $quantity): OrderQuoteEntity
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Quantity must be greater than zero.');
        }

        $group = $this->groups->getForUser($userId);
        $group->assertTradingOpen($assetId, $type);

        $price = $this->prices->getLatest($assetId, $group->id);

        // never trade on an old price
        if (!$price || $price->isStale()) {
            throw new RuntimeException('Live price is not available right now.');
        }

        return $this->quotes->create(
            userId: $userId,
            assetId: $assetId,
            type: $type,
            quantity: $quantity,
            unitPrice: $type === OrderType::BUY ? $price->buyPrice : $price->sellPrice,
            expiresAt: now()->addSeconds(self::QUOTE_TTL_SECONDS)->toDateTimeString(),
        );
    }
}
