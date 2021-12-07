<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Customer;

use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Retrieve customer attribute value
 */
class GetCustomerAttributeValue
{
    /**
     * Returns attribute value
     *
     * @param CustomerInterface $customerEntity
     * @param string $attributeCode
     *
     * @return string
     */
    public function execute(CustomerInterface $customerEntity, string $attributeCode): string
    {
        $method = $this->getMethodByAttributeCode($attributeCode);

        if ($method) {
            return $customerEntity->$method();
        }

        $customAttribute = $customerEntity->getCustomAttribute($attributeCode);

        return $customAttribute ? (string) $customAttribute->getValue() : '';
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
