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
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering the variant_list field
 */
class VariantList implements RenderInterface
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
        $variantList = [];

        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $entity->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getHasChildren()) {
                $variantList[] = $this->getChildrenItemData($orderItem);
            }
        }

        return array_merge([], ...$variantList);
    }

    /**
     * Returns children items data
     *
     * @param OrderItemInterface $orderItem
     *
     * @return array
     */
    private function getChildrenItemData(OrderItemInterface $orderItem): array
    {
        $variantIds = [];
        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $orderItem->getChildrenItems();

        foreach ($childrenItems as $childrenItem) {
            $variantIds[] = [
                'variant_id' => $childrenItem->getProductId(),
                'sku' => $childrenItem->getSku(),
                'quantity' => $childrenItem->getQtyOrdered()
            ];
        }

        return $variantIds;
    }
}
