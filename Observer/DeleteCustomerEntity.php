<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Service\Export\DeleteCustomerEntity as DeleteCustomerEntityService;
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
     * @param DeleteCustomerEntityService $deleteCustomerEntity
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        DeleteCustomerEntityService $deleteCustomerEntity,
        ConfigProvider $configProvider
    ) {
        $this->deleteCustomerEntity = $deleteCustomerEntity;
        $this->configProvider = $configProvider;
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
        if ($this->configProvider->isEnabled()) {
            $event = $observer->getEvent();
            /** @var Customer $customer */
            $customer = $event->getCustomer();
            $this->deleteCustomerEntity->execute($customer);
        }
    }
}
