<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Order;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Order\OrderItem\GetChildProductId;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering the variant_ids field
 */
class VariantIds implements RenderInterface
{
    /**
     * @var GetChildProductId
     */
    private $getChildProductId;

    /**
     * @param GetChildProductId $getChildProductId
     */
    public function __construct(GetChildProductId $getChildProductId)
    {
        $this->getChildProductId = $getChildProductId;
    }

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
        $variantIds = [];

        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $entity->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getHasChildren()) {
                $variantIds[] = $this->getChildrenItemIds($orderItem);
            } elseif (!in_array($orderItem->getProductType(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE])) {
                $variantIds[] = [$orderItem->getProductId()];
            } else {
                $variantIds[] = [$this->getChildProductId->execute($orderItem)];
            }
        }

        return array_merge([], ...$variantIds);
    }

    /**
     * Returns children items ids
     *
     * @param OrderItemInterface $orderItem
     *
     * @return array
     */
    private function getChildrenItemIds(OrderItemInterface $orderItem): array
    {
        $variantIds = [];
        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $orderItem->getChildrenItems();

        foreach ($childrenItems as $childrenItem) {
            $variantIds[] = $childrenItem->getProductId();
        }

        return $variantIds;
    }
}
