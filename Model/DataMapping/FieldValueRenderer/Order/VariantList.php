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
 * The class is responsible for rendering the variant_list field
 */
class VariantList implements RenderInterface
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
        $variantList = [];

        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $entity->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getHasChildren()) {
                $variantList[] = $this->getChildrenItemData($orderItem);
            } else {
                $variantList[] = [$this->getItemData($orderItem)];
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
        $variantList = [];
        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $orderItem->getChildrenItems();

        foreach ($childrenItems as $childrenItem) {
            $variantList[] = $this->getItemData($childrenItem);
        }

        return $variantList;
    }

    /**
     * Returns item Data
     *
     * @param OrderItemInterface $orderItem
     *
     * @return array
     */
    private function getItemData(OrderItemInterface $orderItem): array
    {
        $productId = !in_array($orderItem->getProductType(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE]) ?
            $orderItem->getProductId() : $this->getChildProductId->execute($orderItem);

        return [
            'variant_id' => $productId,
            'sku' => $orderItem->getSku(),
            'quantity' => $orderItem->getQtyOrdered()
        ];
    }
}
