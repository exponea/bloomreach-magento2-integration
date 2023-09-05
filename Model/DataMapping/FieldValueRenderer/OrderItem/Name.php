<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem\ChildItems;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering the value of order item name field
 */
class Name implements RenderInterface
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
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        $name = $entity->getName();

        if ($entity->getProductType() !== Configurable::TYPE_CODE) {
            return $name;
        }

        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $entity->getChildrenItems();

        if (!$childrenItems) {
            $childName = $this->childItems->getChildName(
                (int) $entity->getOrderId(),
                (int) $entity->getItemId()
            );

            return $childName ?: $name;
        }

        foreach ($childrenItems as $childrenItem) {
            return $childrenItem->getName();
        }

        return $name;
    }
}
