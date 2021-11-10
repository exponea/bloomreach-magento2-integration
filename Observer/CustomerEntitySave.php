<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Service\Export\PrepareCustomerDataService;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Get customer entity after save
 */
class CustomerEntitySave implements ObserverInterface
{
    /**
     * @var PrepareCustomerDataService
     */
    private $prepareCustomerDataService;

    /**
     * @param PrepareCustomerDataService $prepareCustomerDataService
     */
    public function __construct(PrepareCustomerDataService $prepareCustomerDataService)
    {
        $this->prepareCustomerDataService = $prepareCustomerDataService;
    }

    /**
     * Get customer entity after save
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Event $event */
        $event = $observer->getEvent();
        /** @var CustomerInterface $customer */
        $customer = $event->getCustomer();

        $this->prepareCustomerDataService->execute($customer);
    }
}
