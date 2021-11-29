<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterfaceFactory;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * The class is responsible for adding delete action to the export queue
 */
class AddDeleteActionToExportQueue
{
    private const API_TYPE = 'delete';

    /**
     * @var ExportQueueInterfaceFactory
     */
    private $exportQueueFactory;

    /**
     * @var SaveExportQueueInterface
     */
    private $saveExportQueue;

    /**
     * @param ExportQueueInterfaceFactory $exportQueueFactory
     * @param SaveExportQueueInterface $saveExportQueue
     */
    public function __construct(
        ExportQueueInterfaceFactory $exportQueueFactory,
        SaveExportQueueInterface $saveExportQueue
    ) {
        $this->exportQueueFactory = $exportQueueFactory;
        $this->saveExportQueue = $saveExportQueue;
    }

    /**
     * Add delete action to the export queue
     *
     * @param string $entityType
     * @param string $entityId
     *
     * @throws CouldNotSaveException
     */
    public function execute(string $entityType, string $entityId)
    {
        /** @var ExportQueueInterface $exportQueue */
        $exportQueue = $this->exportQueueFactory->create();
        $exportQueue->setEntityType($entityType);
        $exportQueue->setApiType(self::API_TYPE);
        $exportQueue->setBody($entityId);
        $this->saveExportQueue->execute($exportQueue);
    }
}
