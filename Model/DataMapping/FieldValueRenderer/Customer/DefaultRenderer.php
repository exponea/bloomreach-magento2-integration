<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Customer;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Customer\GetCustomerAttributeValue;
use Bloomreach\EngagementConnector\Service\EavAttribute\GetAttributeValue;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;

/**
 * The class is responsible for rendering the value of customer field
 */
class DefaultRenderer implements RenderInterface
{
    /**
     * @var GetAttributeValue
     */
    private $getAttributeValue;

    /**
     * @var GetCustomerAttributeValue
     */
    private $getCustomerAttributeValue;

    /**
     * @param GetAttributeValue $getAttributeValue
     * @param GetCustomerAttributeValue $getCustomerAttributeValue
     */
    public function __construct(
        GetAttributeValue $getAttributeValue,
        GetCustomerAttributeValue $getCustomerAttributeValue
    ) {
        $this->getAttributeValue = $getAttributeValue;
        $this->getCustomerAttributeValue = $getCustomerAttributeValue;
    }

    /**
     * Render the value of customer field
     *
     * @param CustomerInterface|Customer $entity
     * @param string $fieldCode
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function render($entity, string $fieldCode)
    {
        if ($entity instanceof CustomerInterface) {
            $attributeValue = $this->getCustomerAttributeValue->execute($entity, $fieldCode);
        } else {
            $attributeValue = $entity->getData($fieldCode);
        }

        return (string) $this->getAttributeValue->execute(
            $attributeValue,
            $fieldCode,
            Customer::ENTITY
        );
    }
}
