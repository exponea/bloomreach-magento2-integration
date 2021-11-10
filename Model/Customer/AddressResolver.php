<?php

namespace Bloomreach\EngagementConnector\Model\Customer;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;

/**
 * The class is responsible for obtaining customer address
 */
class AddressResolver
{
    /**
     * Returns customer address for CustomerInterface
     *
     * @param CustomerInterface $customer
     *
     * @return AddressInterface|null
     */
    public function getAddressForCustomerInterface(CustomerInterface $customer): ?AddressInterface
    {
        $addressId = (int) ($customer->getDefaultShipping() ?: $customer->getDefaultBilling());

        foreach ($customer->getAddresses() as $address) {
            if ($addressId === (int) $address->getId()) {
                return $address;
            }
        }

        return null;
    }

    /**
     * Returns customer address for Customer model
     *
     * @param Customer $customer
     *
     * @return Address|null
     */
    public function getAddressForCustomer(Customer $customer): ?Address
    {
        $address = $customer->getDefaultShippingAddress() ?: $customer->getDefaultBillingAddress();

        return $address ?: null;
    }
}
