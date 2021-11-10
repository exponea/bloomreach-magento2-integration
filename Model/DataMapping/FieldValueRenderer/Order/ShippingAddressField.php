<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Order;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Address\GetCountryName;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order\Address;

/**
 * The class is responsible for rendering the shipping_state field
 */
class ShippingAddressField implements RenderInterface
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
        $field = '';
        /** @var Address $shippingAddress */
        $shippingAddress = $entity->getShippingAddress();

        if ($shippingAddress) {
            $field = $shippingAddress->getData($fieldCode);
        }

        return $field;
    }
}
