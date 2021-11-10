<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\Entity\AddToExportQueue;
use Bloomreach\EngagementConnector\Model\ExportEntityModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity\CollectionFactory;
use Bloomreach\EngagementConnector\Service\ExportEntity\DeleteByEntityIds;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * This class obtains the entities need to be exported, prepares them and adds them to the export queue
 */
class QueueProcessor
{
    private const PAGE_SIZE = 100;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var AddToExportQueue
     */
    private $addToExportQueue;

    /**
     * @var DeleteByEntityIds
     */
    private $deleteByEntityIds;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EntityType $entityType
     * @param CollectionFactory $collectionFactory
     * @param AddToExportQueue $addToExportQueue
     * @param DeleteByEntityIds $deleteByEntityIds
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntityType $entityType,
        CollectionFactory $collectionFactory,
        AddToExportQueue $addToExportQueue,
        DeleteByEntityIds $deleteByEntityIds,
        LoggerInterface $logger
    ) {
        $this->entityType = $entityType;
        $this->collectionFactory = $collectionFactory;
        $this->addToExportQueue = $addToExportQueue;
        $this->deleteByEntityIds = $deleteByEntityIds;
        $this->logger = $logger;
    }

    /**
     * Processes of adding entity types to the export queue
     *
     * @return void
     */
    public function process(): void
    {
        $entityTypes = $this->entityType->getAllTypes();
        foreach ($entityTypes as $entityType) {
            $this->addEntityTypeToExportQueue($entityType);
        }
    }

    /**
     * Adds entities of particular entity type to the export queue
     *
     * @param string $entityType
     *
     * @return void
     */
    private function addEntityTypeToExportQueue(string $entityType): void
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(ExportEntityModel::ENTITY_TYPE, ['eq' => $entityType]);
        $collectionSize = $collection->getSize();

        if (!$collectionSize) {
            return;
        }

        $collection->setPageSize(self::PAGE_SIZE);
        $lastPageNumber = $collection->getLastPageNumber();

        for ($currentPage = $lastPageNumber; $currentPage >= 1; $currentPage--) {
            $collection->setCurPage($currentPage);
            $this->addToExportQueue($entityType, $collection);
        }
    }

    /**
     * Adds entities to the export queue
     *
     * @param string $entityType
     * @param Collection $collection
     *
     * @return void
     */
    private function addToExportQueue(string $entityType, Collection $collection): void
    {
        try {
            $this->addToExportQueue->execute(
                $entityType,
                $collection->getColumnValues(ExportEntityModel::EXPORT_ENTITY_ID)
            );

            $this->deleteByEntityIds->execute(
                $collection->getColumnValues(ExportEntityModel::ENTITY_ID)
            );

            $collection->clear();
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding entities to the export. Error: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
