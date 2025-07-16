<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\OrderItem\TotalPrice as OrderItemTotalPrice;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering price local currency field
 */
class ItemPriceLocalCurrency implements RenderInterface
{
    /**
     * @var OrderItemTotalPrice
     */
    private $orderItemTotalPrice;

    /**
     * @param OrderItemTotalPrice $orderItemTotalPrice
     */
    public function __construct(OrderItemTotalPrice $orderItemTotalPrice)
    {
        $this->orderItemTotalPrice = $orderItemTotalPrice;
    }

    /**
     * Render the value of order item field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        if ((float) $entity->getQtyOrdered() === 0.0) {
            return 0.0;
        }

        return round($this->orderItemTotalPrice->getTotalPriceLocalCurrency($entity) / $entity->getQtyOrdered(), 2);
    }
}
