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
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering the variant_ids field
 */
class VariantIds implements RenderInterface
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
        $variantIds = [];

        /** @var CartItemInterface[] $orderItems */
        $quoteItems = $entity->getAllVisibleItems();

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getChildren()) {
                $variantIds[] = $this->getChildrenItemIds($quoteItem);
            } else {
                $variantIds[] = [$quoteItem->getProductId()];
            }
        }

        return array_merge([], ...$variantIds);
    }

    /**
     * Returns children items ids
     *
     * @param CartItemInterface $quoteItem
     *
     * @return array
     */
    private function getChildrenItemIds(CartItemInterface $quoteItem): array
    {
        $variantIds = [];
        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $quoteItem->getChildren();

        foreach ($childrenItems as $childrenItem) {
            $variantIds[] = $childrenItem->getProductId();
        }

        return $variantIds;
    }
}
