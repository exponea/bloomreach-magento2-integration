<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\OrderItem\GetChildItems;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering original_price amd original_price_local_currency fields
 */
class OriginalPrice implements RenderInterface
{
    /**
     * @var GetChildItems
     */
    private $getChildItems;

    /**
     * @param GetChildItems $getChildItems
     */
    public function __construct(GetChildItems $getChildItems)
    {
        $this->getChildItems = $getChildItems;
    }

    /**
     * Render original_price and original_price_local_currency fields
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        $price = $this->roundPrice((float) $entity->getData($fieldCode));

        if ($entity->getProductType() === BundleType::TYPE_CODE) {
            $price = $this->getBundleProductPrice($entity, $fieldCode);
        }

        return number_format($price, 2);
    }

    /**
     * Calculate bundle item price
     *
     * @param OrderItemInterface $parentItem
     * @param string $fieldCode
     *
     * @return float
     */
    private function getBundleProductPrice(OrderItemInterface $parentItem, string $fieldCode): float
    {
        $total = 0;
        $childItems = $this->getChildItems->execute($parentItem);

        if (!$childItems) {
            return $this->roundPrice((float) $parentItem->getData($fieldCode));
        }

        foreach ($childItems as $orderItem) {
            $total += $this->roundPrice((float) $orderItem->getData($fieldCode));
        }

        return $total;
    }

    /**
     * Round price
     *
     * @param float $price
     *
     * @return float
     */
    private function roundPrice(float $price): float
    {
        return round($price, 2);
    }
}
