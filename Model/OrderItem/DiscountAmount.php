<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\OrderItem;

use Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem\ChildItems;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for calculate discount amount for order item
 */
class DiscountAmount
{
    /**
     * @var ChildItems
     */
    private $childItems;

    /**
     * @var array
     */
    private $cachedData = [];

    /**
     * @param ChildItems $childItems
     */
    public function __construct(ChildItems $childItems)
    {
        $this->childItems = $childItems;
    }

    /**
     * Get base discount amount for order item
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    public function getBaseDiscountAmount(OrderItemInterface $orderItem): float
    {
        return $this->calculateDiscountAmount($orderItem, OrderItemInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Calculate discount amount for order item
     *
     * @param OrderItemInterface $orderItem
     * @param string $type
     * @param bool $perUnit
     *
     * @return float
     *
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    private function calculateDiscountAmount(OrderItemInterface $orderItem, string $type, bool $perUnit = false): float
    {
        $qtyOrdered = $perUnit ? $orderItem->getQtyOrdered() : 1;
        $discount = $this->roundDiscount((float) ($orderItem->getData($type) / $qtyOrdered));

        if ($orderItem->getProductType() !== BundleType::TYPE_CODE) {
            return $discount;
        }

        $discount = 0;

        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $orderItem->getChildrenItems();

        if ($childrenItems) {
            $qtyOrdered = $perUnit ? $orderItem->getQtyOrdered() : 1;
            foreach ($childrenItems as $childrenItem) {
                $discount += $childrenItem->getData($type);
            }

            return $this->roundDiscount((float) $discount / $qtyOrdered);
        }

        // Used if order items were loaded through a collection
        $childItemsData = $this->getChildItemsData((int) $orderItem->getItemId());

        foreach ($childItemsData as $childItem) {
            $discountAmount = $childItem[$type] ?? 0.0;
            $qtyOrdered = $perUnit ? ($childItem[OrderItemInterface::QTY_ORDERED] ?? 1) : 1;
            $discount += $discountAmount / $qtyOrdered;
        }

        return $this->roundDiscount((float) $discount);
    }

    /**
     * Round discount
     *
     * @param float $discount
     *
     * @return float
     */
    private function roundDiscount(float $discount): float
    {
        return round($discount, 2);
    }

    /**
     * Returns child item data from database
     *
     * @param int $orderItemId
     *
     * @return array
     */
    private function getChildItemsData(int $orderItemId): array
    {
        if (!array_key_exists($orderItemId, $this->cachedData)) {
            $this->cachedData = [
                $orderItemId => $this->childItems->getDiscountData($orderItemId)
            ];
        }

        return $this->cachedData[$orderItemId];
    }

    /**
     * Returns discount amount
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    public function getDiscountAmount(OrderItemInterface $orderItem): float
    {
        return $this->calculateDiscountAmount($orderItem, OrderItemInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Get discount amount for order item
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    public function getDiscountAmountPerUnit(OrderItemInterface $orderItem): float
    {
        return $this->calculateDiscountAmount($orderItem, OrderItemInterface::DISCOUNT_AMOUNT, true);
    }

    /**
     * Get base discount amount for order item
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    public function getBaseDiscountAmountPerUnit(OrderItemInterface $orderItem): float
    {
        return $this->calculateDiscountAmount($orderItem, OrderItemInterface::BASE_DISCOUNT_AMOUNT, true);
    }
}
