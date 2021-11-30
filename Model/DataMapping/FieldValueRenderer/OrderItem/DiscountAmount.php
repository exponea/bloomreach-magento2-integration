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
 * The class is responsible for rendering the value of order item discount amount field
 */
class DiscountAmount implements RenderInterface
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
     * Render the value of order item field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        return $this->discountAmount->getBaseDiscountAmountPerUnit($entity);
    }
}
