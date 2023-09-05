<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer as CustomerDataMapper;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Order as OrderDataMapper;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\OrderItem as OrderItemDataMapper;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Service\Export\ExportGuestCustomer;
use Bloomreach\EngagementConnector\Service\Export\PrepareOrderDataService;
use Bloomreach\EngagementConnector\Service\Export\PrepareOrderItemsDataService;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Get order entity after save
 */
class OrderEntitySave implements ObserverInterface
{
    /**
     * @var PrepareOrderDataService
     */
    private $prepareOrderDataService;

    /**
     * @var PrepareOrderItemsDataService
     */
    private $prepareOrderItemsDataService;

    /**
     * @var ExportGuestCustomer
     */
    private $exportGuestCustomer;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @param PrepareOrderDataService $prepareOrderDataService
     * @param PrepareOrderItemsDataService $prepareOrderItemsDataService
     * @param ExportGuestCustomer $exportGuestCustomer
     * @param ConfigProvider $configProvider
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     */
    public function __construct(
        PrepareOrderDataService $prepareOrderDataService,
        PrepareOrderItemsDataService $prepareOrderItemsDataService,
        ExportGuestCustomer $exportGuestCustomer,
        ConfigProvider $configProvider,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
    ) {
        $this->prepareOrderDataService = $prepareOrderDataService;
        $this->prepareOrderItemsDataService = $prepareOrderItemsDataService;
        $this->exportGuestCustomer = $exportGuestCustomer;
        $this->configProvider = $configProvider;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
    }

    /**
     * Get order entity after save
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $event = $observer->getEvent();
        /** @var Order $order */
        $order = $event->getOrder();

        if ($this->isRealTimeUpdateAllowed->execute(CustomerDataMapper::ENTITY_TYPE)) {
            $this->exportGuestCustomer->execute($order);
        }

        if ($this->isRealTimeUpdateAllowed->execute(OrderDataMapper::ENTITY_TYPE)) {
            $this->prepareOrderDataService->execute($order);
        }

        if ($this->isRealTimeUpdateAllowed->execute(OrderItemDataMapper::ENTITY_TYPE)) {
            $this->prepareOrderItemsDataService->execute($order);
        }
    }
}
