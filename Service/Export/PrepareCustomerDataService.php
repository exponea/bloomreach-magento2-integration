<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer as CustomerDataMapper;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddEventToExportQueue;
use Magento\Customer\Model\Customer;
use Psr\Log\LoggerInterface;

/**
 * The class responsible to preparing customer entity data after save
 */
class PrepareCustomerDataService
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
     * Preparing customer entity data after save
     *
     * @param Customer $customer
     *
     * @return void
     */
    public function execute(Customer $customer): void
    {
        try {
            $this->addEventToExportQueue->execute(
                CustomerDataMapper::ENTITY_TYPE,
                $this->registeredGenerator->generateSerialized(
                    $customer->getEmail(),
                    $customer->getEntityId() ? (int) $customer->getEntityId() : null
                ),
                $customer
            );
        } catch (\Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding Customer update event to the export queue. Error: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
