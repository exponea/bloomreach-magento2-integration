<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Customer;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Customer\GetCustomerAttributeValue;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * The class is responsible for render and convert birth_date field for customer
 */
class Timestamp implements RenderInterface
{
    /**
     * Offset in seconds (12h)
     */
    private const OFFSET = 43200;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var GetCustomerAttributeValue
     */
    private $getCustomerAttributeValue;

    /**
     * @param DateTime $dateTime
     * @param GetCustomerAttributeValue $getCustomerAttributeValue
     */
    public function __construct(
        DateTime $dateTime,
        GetCustomerAttributeValue $getCustomerAttributeValue
    ) {
        $this->dateTime = $dateTime;
        $this->getCustomerAttributeValue = $getCustomerAttributeValue;
    }

    /**
     * Render and convert birth_date field
     *
     * @param AbstractSimpleObject|AbstractModel|CustomerInterface $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        if ($entity instanceof CustomerInterface) {
            $attributeValue = $this->getCustomerAttributeValue->execute($entity, $fieldCode);
        } else {
            $attributeValue = (string) $entity->getData($fieldCode);
        }

        return $attributeValue ? (string) ($this->dateTime->timestamp($attributeValue) + self::OFFSET) : '';
    }
}
