<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer as CustomerDataMapper;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Service\Export\PrepareCustomerDataService;
use Bloomreach\EngagementConnector\System\ConfigProvider;
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
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @param PrepareCustomerDataService $prepareCustomerDataService
     * @param ConfigProvider $configProvider
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     */
    public function __construct(
        PrepareCustomerDataService $prepareCustomerDataService,
        ConfigProvider $configProvider,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
    ) {
        $this->prepareCustomerDataService = $prepareCustomerDataService;
        $this->configProvider = $configProvider;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
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
        if (!$this->configProvider->isEnabled()
            || !$this->isRealTimeUpdateAllowed->execute(CustomerDataMapper::ENTITY_TYPE)
        ) {
            return;
        }

        $event = $observer->getEvent();
        /** @var Customer $customer */
        $customer = $event->getCustomer();

        $this->prepareCustomerDataService->execute($customer);
    }
}
