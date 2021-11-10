<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem\ChildItems;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering the variant_id field
 */
class VariantId implements RenderInterface
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
     * Render the value of order item field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return array
     */
    public function render($entity, string $fieldCode)
    {
        $variantIds = [];

        if (!in_array($entity->getProductType(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE])) {
            return [$entity->getProductId()];
        }

        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $entity->getChildrenItems();

        if (!$childrenItems) {
            return $this->childItems->getChildIds((int) $entity->getItemId());
        }

        foreach ($childrenItems as $childrenItem) {
            $variantIds[] = $childrenItem->getProductId();
        }

        return $variantIds;
    }
}
