<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddEventToExportQueue;
use Bloomreach\EngagementConnector\Service\Customer\CreateGuestModelByOrder;
use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for adding guest customer to export queue after placing the order
 */
class ExportGuestCustomer
{
    private const ENTITY_TYPE = 'customer';

    /**
     * @var CreateGuestModelByOrder
     */
    private $createGuestModelByOrder;

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
     * @param CreateGuestModelByOrder $createGuestModelByOrder
     * @param AddEventToExportQueue $addEventToExportQueue
     * @param RegisteredGenerator $registeredGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        CreateGuestModelByOrder $createGuestModelByOrder,
        AddEventToExportQueue $addEventToExportQueue,
        RegisteredGenerator $registeredGenerator,
        LoggerInterface $logger
    ) {
        $this->createGuestModelByOrder = $createGuestModelByOrder;
        $this->addEventToExportQueue = $addEventToExportQueue;
        $this->registeredGenerator = $registeredGenerator;
        $this->logger = $logger;
    }

    /**
     * Adds guest customer to the export queue
     *
     * @param OrderInterface $order
     *
     * @return void
     */
    public function execute(OrderInterface $order): void
    {
        if (!$order->getCustomerIsGuest()) {
            return;
        }

        try {
            $this->addEventToExportQueue->execute(
                self::ENTITY_TYPE,
                $this->registeredGenerator->generateSerialized($order->getCustomerEmail(), null),
                $this->createGuestModelByOrder->execute($order)
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding guest customer to the export queue. Error: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
