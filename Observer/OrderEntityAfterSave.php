<?php

/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Service\Export\ExportGuestCustomer;
use Bloomreach\EngagementConnector\Service\Export\PrepareOrderDataService;
use Bloomreach\EngagementConnector\Service\Export\PrepareOrderItemsDataService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

/**
 * Get order entity after save
 */
class OrderEntityAfterSave implements ObserverInterface
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
     * @param PrepareOrderDataService $prepareOrderDataService
     * @param PrepareOrderItemsDataService $prepareOrderItemsDataService
     * @param ExportGuestCustomer $exportGuestCustomer
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        PrepareOrderDataService $prepareOrderDataService,
        PrepareOrderItemsDataService $prepareOrderItemsDataService,
        ExportGuestCustomer $exportGuestCustomer,
        ConfigProvider $configProvider,
    ) {
        $this->prepareOrderDataService = $prepareOrderDataService;
        $this->prepareOrderItemsDataService = $prepareOrderItemsDataService;
        $this->exportGuestCustomer = $exportGuestCustomer;
        $this->configProvider = $configProvider;
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
        if ($this->configProvider->isEnabled()) {
            $event = $observer->getEvent();
            /** @var Order $order */
            $order = $event->getOrder();

            $order['purchase_status'] = $order->getState();

            $this->exportGuestCustomer->execute($order);
            $this->prepareOrderDataService->execute($order);
            $this->prepareOrderItemsDataService->execute($order);
        }
    }
}
