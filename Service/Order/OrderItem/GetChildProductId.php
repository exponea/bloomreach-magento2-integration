<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Order\OrderItem;

use Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem\ChildItems;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for obtaining child product id for configurable product type
 */
class GetChildProductId
{
    /**
     * @var ChildItems
     */
    private $childItems;

    /**
     * @param ChildItems $childItems
     */
    public function __construct(ChildItems $childItems)
    {
        $this->childItems = $childItems;
    }

    /**
     * Returns product id using order item product options
     *
     * @param OrderItemInterface $orderItem
     *
     * @return string
     */
    public function execute(OrderItemInterface $orderItem): string
    {
        if ($orderItem->getProductType() !== Configurable::TYPE_CODE) {
            return '';
        }

        $variantId = '';

        $productOptions = $orderItem->getData('product_options');

        if (is_array($productOptions)) {
            $sku = $productOptions['simple_sku'] ?? '';
            $variantId = $this->childItems->getProductIdBySku($sku);
        }

        return $variantId;
    }
}
