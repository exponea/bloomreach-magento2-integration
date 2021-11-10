<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\OrderItem\DiscountAmount as OrderItemDiscountAmount;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering total price field
 */
class TotalPrice implements RenderInterface
{
    /**
     * @var OrderItemDiscountAmount
     */
    private $discountAmount;

    /**
     * @param OrderItemDiscountAmount $discountAmount
     */
    public function __construct(OrderItemDiscountAmount $discountAmount)
    {
        $this->discountAmount = $discountAmount;
    }

    /**
     * Render the value of total price field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return float
     */
    public function render($entity, string $fieldCode)
    {
        return round(
            $entity->getBaseRowTotal() - $this->discountAmount->getBaseDiscountAmount($entity)
            + $entity->getBaseTaxAmount(),
            2
        );
    }
}
