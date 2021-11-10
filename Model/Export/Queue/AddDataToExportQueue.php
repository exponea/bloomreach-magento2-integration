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
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use InvalidArgumentException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for adding an array of entities to the export queue
 */
class AddDataToExportQueue
{
    private const API_TYPE = 'csv_export';

    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

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
     * @param DataMapperResolver $dataMapperResolver
     * @param SerializerInterface $jsonSerializer
     * @param ExportQueueInterfaceFactory $exportQueueFactory
     * @param SaveExportQueueInterface $saveExportQueue
     */
    public function __construct(
        DataMapperResolver $dataMapperResolver,
        SerializerInterface $jsonSerializer,
        ExportQueueInterfaceFactory $exportQueueFactory,
        SaveExportQueueInterface $saveExportQueue
    ) {
        $this->dataMapperResolver = $dataMapperResolver;
        $this->jsonSerializer = $jsonSerializer;
        $this->exportQueueFactory = $exportQueueFactory;
        $this->saveExportQueue = $saveExportQueue;
    }

    /**
     * Adds data to the export queue
     *
     * @param string $entityType
     * @param array $data
     *
     * @return void
     * @throws ConfigurationMismatchException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     */
    public function execute(string $entityType, array $data): void
    {
        $body = [];
        foreach ($data as $item) {
            $body[] = $this->dataMapperResolver->map($item, $entityType)->toArray();
        }

        if ($body) {
            /** @var ExportQueueInterface $exportQueue */
            $exportQueue = $this->exportQueueFactory->create();
            $exportQueue->setEntityType($entityType);
            $exportQueue->setApiType(self::API_TYPE);
            $exportQueue->setBody($this->jsonSerializer->serialize($body));
            $this->saveExportQueue->execute($exportQueue);
        }
    }
}
