<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer as CustomerDataMapper;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Service\Export\DeleteCustomerEntity as DeleteCustomerEntityService;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Customer\Model\Customer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * The class is responsible for delete customer entity on the Bloomreach side
 */
class DeleteCustomerEntity implements ObserverInterface
{
    /**
     * @var DeleteCustomerEntityService
     */
    private $deleteCustomerEntity;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @param DeleteCustomerEntityService $deleteCustomerEntity
     * @param ConfigProvider $configProvider
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     */
    public function __construct(
        DeleteCustomerEntityService $deleteCustomerEntity,
        ConfigProvider $configProvider,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
    ) {
        $this->deleteCustomerEntity = $deleteCustomerEntity;
        $this->configProvider = $configProvider;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
    }

    /**
     * Delete customer entity on the Bloomreach side
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
        $this->deleteCustomerEntity->execute($customer);
    }
}
