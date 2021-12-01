<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\OrderItem;

use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Weee\Helper\Data;

/**
 * The class is responsible for calculating order item total price
 */
class TotalPrice
{
    /**
     * @var GetChildItems
     */
    private $getChildItems;

    /**
     * @var Data
     */
    private $weeeHelper;

    /**
     * @param GetChildItems $getChildItems
     * @param Data $weeeHelper
     */
    public function __construct(
        GetChildItems $getChildItems,
        Data $weeeHelper
    ) {
        $this->getChildItems = $getChildItems;
        $this->weeeHelper = $weeeHelper;
    }

    /**
     * Returns order item total in local currency
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    public function getTotalPriceLocalCurrency(OrderItemInterface $orderItem): float
    {
        if ($orderItem->getProductType() !== BundleType::TYPE_CODE) {
            return $this->getItemTotalPriceLocalCurrency($orderItem);
        }

        $total = 0;
        $orderItems = $this->getChildItems->execute($orderItem);

        foreach ($orderItems as $orderItem) {
            $total += $this->getItemTotalPriceLocalCurrency($orderItem);
        }

        return $total;
    }

    /**
     * Returns order item total in base currency
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    public function getTotalPriceBaseCurrency(OrderItemInterface $orderItem): float
    {
        if ($orderItem->getProductType() !== BundleType::TYPE_CODE) {
            return $this->getItemTotalPriceBaseCurrency($orderItem);
        }

        $total = 0;
        $orderItems = $this->getChildItems->execute($orderItem);

        foreach ($orderItems as $orderItem) {
            $total += $this->getItemTotalPriceBaseCurrency($orderItem);
        }

        return $total;
    }

    /**
     * Returns item total in base currency
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    private function getItemTotalPriceBaseCurrency(OrderItemInterface $orderItem): float
    {
        return $orderItem->getBaseRowTotal()
            - $orderItem->getBaseDiscountAmount()
            + $orderItem->getBaseTaxAmount()
            + $orderItem->getBaseDiscountTaxCompensationAmount()
            + $this->weeeHelper->getBaseRowWeeeTaxInclTax($orderItem);
    }

    /**
     * Returns order item total in local currency
     *
     * @param OrderItemInterface $orderItem
     *
     * @return float
     */
    private function getItemTotalPriceLocalCurrency(OrderItemInterface $orderItem): float
    {
        return $orderItem->getRowTotal()
            - $orderItem->getDiscountAmount()
            + $orderItem->getTaxAmount()
            + $orderItem->getDiscountTaxCompensationAmount()
            + $this->weeeHelper->getRowWeeeTaxInclTax($orderItem);
    }
}
