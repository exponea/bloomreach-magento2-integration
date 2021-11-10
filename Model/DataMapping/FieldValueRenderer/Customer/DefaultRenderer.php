<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Customer;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
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
     * @param GetAttributeValue $getAttributeValue
     */
    public function __construct(GetAttributeValue $getAttributeValue)
    {
        $this->getAttributeValue = $getAttributeValue;
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
            $attributeValue = $this->getAttributeValue($entity, $fieldCode);
        } else {
            $attributeValue = $entity->getData($fieldCode);
        }

        return (string) $this->getAttributeValue->execute(
            $attributeValue,
            $fieldCode,
            Customer::ENTITY
        );
    }

    /**
     * Returns attribute value
     *
     * @param CustomerInterface $customerEntity
     * @param string $attributeCode
     *
     * @return mixed|string|null
     */
    private function getAttributeValue(CustomerInterface $customerEntity, string $attributeCode)
    {
        $method = $this->getMethodByAttributeCode($attributeCode);

        if ($method) {
            return $customerEntity->$method();
        }

        $customAttribute = $customerEntity->getCustomAttribute($attributeCode);

        return $customAttribute ? $customAttribute->getValue() : '';
    }

    /**
     * Returns Customer interface method for attribute
     *
     * @param string $attributeCode
     *
     * @return string
     */
    private function getMethodByAttributeCode(string $attributeCode): string
    {
        return $this->getAttributeToMethodArray()[$attributeCode] ?? '';
    }

    /**
     * Returns array attribute and method
     *
     * @return string[]
     */
    private function getAttributeToMethodArray(): array
    {
        return [
            'entity_id' => 'getId',
            'confirmation' => 'getConfirmation',
            'created_at' => 'getCreatedAt',
            'updated_at' => 'getUpdatedAt',
            'dob' => 'getDob',
            'email' => 'getEmail',
            'firstname' => 'getFirstname',
            'gender' => 'getGender',
            'group_id' => 'getGroupId',
            'lastname' => 'getLastName',
            'middlename' => 'getMiddlename',
            'prefix' => 'getPrefix',
            'store_id' => 'getStoreId',
            'suffix' => 'getSuffix',
            'taxvat' => 'getTaxvat',
            'website_id' => 'getWebsiteId',
            'default_billing' => 'getDefaultBilling',
            'default_shipping' => 'getDefaultShipping'
        ];
    }
}
