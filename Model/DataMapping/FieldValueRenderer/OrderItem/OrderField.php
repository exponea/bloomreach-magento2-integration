<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * The class is responsible for rendering the value of order item field from order object
 */
class OrderField implements RenderInterface
{
    /**
     * Render the value of order field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        /** @var OrderInterface $order */
        $order = $entity->getOrder();

        return $order ? (string) $order->getData($fieldCode) : '';
    }
}
