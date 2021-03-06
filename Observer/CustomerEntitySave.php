<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Service\Export\PrepareCustomerDataService;
use Magento\Customer\Model\Customer;
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
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param PrepareCustomerDataService $prepareCustomerDataService
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        PrepareCustomerDataService $prepareCustomerDataService,
        ConfigProvider $configProvider
    ) {
        $this->prepareCustomerDataService = $prepareCustomerDataService;
        $this->configProvider = $configProvider;
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
        if ($this->configProvider->isEnabled()) {
            $event = $observer->getEvent();
            /** @var Customer $customer */
            $customer = $event->getCustomer();

            $this->prepareCustomerDataService->execute($customer);
        }
    }
}
