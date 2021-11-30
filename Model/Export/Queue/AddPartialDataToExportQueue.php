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
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for adding entity partial data to the export queue
 */
class AddPartialDataToExportQueue
{
    private const API_TYPE = 'partial_update';

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ExportQueueInterfaceFactory
     */
    private $exportQueueFactory;

    /**
     * @var SaveExportQueueInterface
     */
    private $saveExportQueue;

    /**
     * @param SerializerInterface $jsonSerializer
     * @param ExportQueueInterfaceFactory $exportQueueFactory
     * @param SaveExportQueueInterface $saveExportQueue
     */
    public function __construct(
        SerializerInterface $jsonSerializer,
        ExportQueueInterfaceFactory $exportQueueFactory,
        SaveExportQueueInterface $saveExportQueue
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->exportQueueFactory = $exportQueueFactory;
        $this->saveExportQueue = $saveExportQueue;
    }

    /**
     * Add partial data to export queue
     *
     * @param string $entityType
     * @param array $data
     *
     * @throws CouldNotSaveException
     */
    public function execute(string $entityType, array $data): void
    {
        /** @var ExportQueueInterface $exportQueue */
        $exportQueue = $this->exportQueueFactory->create();
        $exportQueue->setEntityType($entityType);
        $exportQueue->setApiType(self::API_TYPE);
        $exportQueue->setBody($this->jsonSerializer->serialize($data));
        $this->saveExportQueue->execute($exportQueue);
    }
}
