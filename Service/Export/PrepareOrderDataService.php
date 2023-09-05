<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Order;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddEventToExportQueue;
use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * The class responsible to preparing order entity data after save
 */
class PrepareOrderDataService
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
     * Preparing order entity data after save
     *
     * @param OrderInterface $order
     *
     * @return void
     */
    public function execute(OrderInterface $order): void
    {
        try {
            $this->addEventToExportQueue->execute(
                Order::ENTITY_TYPE,
                $this->registeredGenerator->generateSerialized(
                    $order->getCustomerEmail(),
                    $order->getCustomerId() ? (int) $order->getCustomerId() : null
                ),
                $order
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding Purchase event to the export queue. Error: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
