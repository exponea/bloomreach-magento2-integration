<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Order;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering the product_ids field
 */
class ProductIds implements RenderInterface
{
    /**
     * Render the value of order field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return array
     */
    public function render($entity, string $fieldCode)
    {
        $productIds = [];

        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $entity->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            if (in_array($orderItem->getProductId(), $productIds)) {
                continue;
            }
            
            $productIds[] = $orderItem->getProductId();
        }

        return $productIds;
    }
}
