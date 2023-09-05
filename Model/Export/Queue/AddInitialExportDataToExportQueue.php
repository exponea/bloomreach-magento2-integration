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
 * The class is responsible for adding an array of entities to the export queue
 */
class AddInitialExportDataToExportQueue
{
    public const API_TYPE = 'csv_export';

    public const FIRST_ID_IN_FILE = 'first_id_in_file';

    public const LAST_ID_IN_FILE = 'last_id_in_file';

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
     * Adds data to the export queue
     *
     * @param string $entityType
     * @param string $body
     * @param int $numberOfItems
     * @param array $additionalData
     *
     * @return void
     * @throws CouldNotSaveException
     */
    public function execute(string $entityType, string $body, int $numberOfItems, array $additionalData): void
    {
        /** @var ExportQueueInterface $exportQueue */
        $exportQueue = $this->exportQueueFactory->create();
        $exportQueue->setEntityType($entityType);
        $exportQueue->setApiType(self::API_TYPE);
        $exportQueue->setNumberOfItems($numberOfItems);
        $exportQueue->setBody($body);
        if ($additionalData) {
            $exportQueue->setAdditionalData($this->jsonSerializer->serialize($additionalData));
        }

        $this->saveExportQueue->execute($exportQueue);
    }
}
