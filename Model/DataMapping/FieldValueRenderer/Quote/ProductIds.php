<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Quote;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * The class is responsible for rendering the product_ids field
 */
class ProductIds implements RenderInterface
{
    /**
     * Render the value of quote field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return array
     */
    public function render($entity, string $fieldCode)
    {
        $productIds = [];

        /** @var CartItemInterface[] $orderItems */
        $quoteItems = $entity->getAllVisibleItems();

        foreach ($quoteItems as $quoteItem) {
            if (in_array($quoteItem->getProductId(), $productIds)) {
                continue;
            }
            
            $productIds[] = $quoteItem->getProductId();
        }

        return $productIds;
    }
}
