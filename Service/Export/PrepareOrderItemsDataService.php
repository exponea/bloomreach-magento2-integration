<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\OrderItem;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddEventToExportQueue;
use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Psr\Log\LoggerInterface;

/**
 * The class responsible to preparing order items entity data after save order
 */
class PrepareOrderItemsDataService
{
    /**
     * @var AddEventToExportQueue
     */
    private $addEventToExportQueue;

    /**
     * @var RegisteredGenerator
     */
    private $registeredGenerator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AddEventToExportQueue $addEventToExportQueue
     * @param RegisteredGenerator $registeredGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddEventToExportQueue $addEventToExportQueue,
        RegisteredGenerator $registeredGenerator,
        LoggerInterface $logger
    ) {
        $this->addEventToExportQueue = $addEventToExportQueue;
        $this->registeredGenerator = $registeredGenerator;
        $this->logger = $logger;
    }

    /**
     * Preparing order items entity data after save order
     *
     * @param OrderInterface $order
     *
     * @return void
     */
    public function execute(OrderInterface $order): void
    {
        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $order->getAllVisibleItems();
        $registered = $this->registeredGenerator->generateSerialized(
            $order->getCustomerEmail(),
            $order->getCustomerId() ? (int) $order->getCustomerId() : null
        );

        try {
            foreach ($orderItems as $orderItem) {
                $this->addEventToExportQueue->execute(OrderItem::ENTITY_TYPE, $registered, $orderItem);
            }
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding Purchase Item event to the export queue. Error: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
