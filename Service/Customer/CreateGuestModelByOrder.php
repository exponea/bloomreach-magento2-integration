<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Customer;

use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * The class is responsible for creating customer model by order data
 */
class CreateGuestModelByOrder
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @param CustomerFactory $customerFactory
     */
    public function __construct(CustomerFactory $customerFactory)
    {
        $this->customerFactory = $customerFactory;
    }

    /**
     * Create customer model by order data
     *
     * @param OrderInterface $order
     *
     * @return Customer
     */
    public function execute(OrderInterface $order): Customer
    {
        /** @var Customer $customer */
        $customer = $this->customerFactory->create();
        $address = $order->getShippingAddress() ?: $order->getBillingAddress();

        $customerData = [
            'email' => $order->getCustomerEmail(),
            'firstname' => $order->getCustomerFirstname(),
            'lastname' => $order->getCustomerLastname(),
        ];

        if ($address) {
            $customerData['country'] = $address->getCountryId();
            $customerData['region'] = $address->getRegion();
            $customerData['company'] = $address->getCompany();
            $customerData['telephone'] = $address->getTelephone();

        }

        $customer->setData($customerData);

        return $customer;
    }
}
