<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterfaceFactory;
use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\SaveInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsInitialExportAllowed;
use Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory as EntityCollectionFactory;
use Bloomreach\EngagementConnector\Model\Export\Entity\ProductCollection;
use Bloomreach\EngagementConnector\Model\Export\File\FileNameGenerator;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Registry\ExportQueue as ExportQueueRegistry;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\AddNewErrorMessage;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Exception;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\FileSystemException;
use Psr\Log\LoggerInterface;

/**
 * This class obtains the entities need to be exported, prepares them and adds them to the export queue
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class QueueProcessor
{
    private const PAGE_SIZE = 1000;

    private const MAX_NUMBER_OF_ITEMS_IN_FILE = 100000;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var EntityCollectionFactory
     */
    private $entityCollectionFactory;

    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

    /**
     * @var ExportFileProcessor
     */
    private $exportFileProcessor;

    /**
     * @var IsInitialExportAllowed
     */
    private $isInitialExportAllowed;

    /**
     * @var InitialExportStatusGetter
     */
    private $initialExportStatusGetter;

    /**
     * @var SaveInitialExportStatusInterface
     */
    private $saveInitialExportStatus;

    /**
     * @var FileNameGenerator
     */
    private $fileNameGenerator;

    /**
     * @var AddInitialExportDataToExportQueue
     */
    private $addInitialExportDataToExportQueue;

    /**
     * @var AddNewErrorMessage
     */
    private $addNewErrorMessage;

    /**
     * @var array
     */
    private $additionalExportData = [];

    /**
     * @var int
     */
    private $numberOfItemsInExportFile;

    /**
     * @var string
     */
    private $exportFileName;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ExportQueueRegistry
     */
    private $exportQueueRegistry;

    /**
     * @param EntityType $entityType
     * @param EntityCollectionFactory $entityCollectionFactory
     * @param DataMapperResolver $dataMapperResolver
     * @param ExportFileProcessor $exportFileProcessor
     * @param FileNameGenerator $fileNameGenerator
     * @param IsInitialExportAllowed $isInitialExportAllowed
     * @param InitialExportStatusGetter $initialExportStatusGetter
     * @param SaveInitialExportStatusInterface $saveInitialExportStatus
     * @param AddInitialExportDataToExportQueue $addInitialExportDataToExportQueue
     * @param AddNewErrorMessage $addNewErrorMessage
     * @param LoggerInterface $logger
     * @param ExportQueueRegistry $exportQueueRegistry
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityType $entityType,
        EntityCollectionFactory $entityCollectionFactory,
        DataMapperResolver $dataMapperResolver,
        ExportFileProcessor $exportFileProcessor,
        FileNameGenerator $fileNameGenerator,
        IsInitialExportAllowed $isInitialExportAllowed,
        InitialExportStatusGetter $initialExportStatusGetter,
        SaveInitialExportStatusInterface $saveInitialExportStatus,
        AddInitialExportDataToExportQueue $addInitialExportDataToExportQueue,
        AddNewErrorMessage $addNewErrorMessage,
        LoggerInterface $logger,
        ExportQueueRegistry $exportQueueRegistry
    ) {
        $this->entityType = $entityType;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->dataMapperResolver = $dataMapperResolver;
        $this->exportFileProcessor = $exportFileProcessor;
        $this->isInitialExportAllowed = $isInitialExportAllowed;
        $this->initialExportStatusGetter = $initialExportStatusGetter;
        $this->saveInitialExportStatus = $saveInitialExportStatus;
        $this->fileNameGenerator = $fileNameGenerator;
        $this->addInitialExportDataToExportQueue = $addInitialExportDataToExportQueue;
        $this->addNewErrorMessage = $addNewErrorMessage;
        $this->logger = $logger;
        $this->exportQueueRegistry = $exportQueueRegistry;
    }

    /**
     * Processes of adding entity types to the export queue
     *
     * @return void
     */
    public function process(): void
    {
        //Retrieve each entity separately to prevent it from being processed by another cron process
        foreach ($this->entityType->getAllTypes() as $entityType) {
            $initialExportStatus = $this->initialExportStatusGetter->execute($entityType);
            if ($initialExportStatus->getStatus() === StatusSource::SCHEDULED
                && !$initialExportStatus->isLocked()
                && $this->isInitialExportAllowed->execute($initialExportStatus->getEntityType())
            ) {
                $this->addEntityTypeToExportQueue($initialExportStatus);
            }
        }
    }

    /**
     * Adds entities of particular entity type to the export queue
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return void
     */
    private function addEntityTypeToExportQueue(InitialExportStatusInterface $initialExportStatus): void
    {
        //Locks entity to prevent it from being processed by another cron process
        $initialExportStatus->setIsLocked(true);
        $this->saveInitialExportStatus->execute($initialExportStatus);

        $collection = $this->entityCollectionFactory->create($initialExportStatus->getEntityType());
        $collectionSize = (int) $collection->getSize();

        //Update total items and set processing status
        $initialExportStatus->setTotalItems($collectionSize);
        $initialExportStatus->setStatus(StatusSource::PROCESSING);
        $this->saveInitialExportStatus->execute($initialExportStatus);

        try {
            //Adds entities to the export queue
            $this->addToExportQueue($initialExportStatus, $collection);
        } catch (Exception $e) {
            $initialExportStatus->setStatus(StatusSource::ERROR);
            $this->logger->error(
                __(
                    'An error occurred while adding entities to the export. Error: %1',
                    $e->getMessage()
                )
            );
        } finally {
            //Unlocks entity and save progress
            $initialExportStatus->setIsLocked(false);
            $this->saveInitialExportStatus->execute($initialExportStatus);
        }
    }

    /**
     * Adds entities to the export queue
     *
     * @param InitialExportStatusInterface $initialExportStatus
     * @param AbstractDb $collection
     *
     * @return void
     * @throws Exception
     */
    private function addToExportQueue(InitialExportStatusInterface $initialExportStatus, AbstractDb $collection): void
    {
        $collection->setPageSize(self::PAGE_SIZE);
        $lastPageNumber = $collection->getLastPageNumber();
        $lastLoadedItemId = 0;

        for ($currentPage = 1; $currentPage <= $lastPageNumber; $currentPage++) {
            // Recreate collection object ot avoid issues with filter
            $collection = $this->entityCollectionFactory->create($initialExportStatus->getEntityType());
            $this->setFilters($collection, $lastLoadedItemId);

            $result = $this->addBatchToExportQueue(
                $initialExportStatus,
                $collection,
                $currentPage === $lastPageNumber
            );

            if (!$result) {
                return;
            }

            $lastLoadedItemId = $this->getLastLoadedItemId($collection);
        }
    }

    /**
     * Add batch of collection to export queue
     *
     * @param InitialExportStatusInterface $initialExportStatus
     * @param AbstractDb $collection
     * @param bool $isLastBatch
     *
     * @return void
     * @throws Exception
     */
    private function addBatchToExportQueue(
        InitialExportStatusInterface $initialExportStatus,
        AbstractDb $collection,
        bool $isLastBatch
    ): bool {
        $size = 0;

        try {
            $entityType = $initialExportStatus->getEntityType();
            $size = $collection->count();

            if (!$size) {
                return false;
            }

            $firstItemId = $collection->getFirstItem()->getData($this->getEntityIdFieldName($collection));
            $lastItemId = $collection->getLastItem()->getData($this->getEntityIdFieldName($collection));

            if ($this->getNumberOfItemsInExportFile() === 0) {
                $this->resetAdditionalExportData();
                $this->setExportFileName($this->fileNameGenerator->execute());
                $this->addItemToAdditionalExportData(
                    AddInitialExportDataToExportQueue::FIRST_ID_IN_FILE,
                    min($firstItemId, $lastItemId)
                );
            }

            $data = $this->mapItems($entityType, $collection->getItems());
            $fullFileName = $this->exportFileProcessor->process($data, $entityType, $this->getExportFileName());
            $this->setNumberOfItemsInExportFile($this->getNumberOfItemsInExportFile() + $size);

            if ($this->getNumberOfItemsInExportFile() >= self::MAX_NUMBER_OF_ITEMS_IN_FILE || $isLastBatch) {
                $this->addItemToAdditionalExportData(
                    AddInitialExportDataToExportQueue::LAST_ID_IN_FILE,
                    max($firstItemId, $lastItemId)
                );
                $this->addInitialExportDataToExportQueue->execute(
                    $entityType,
                    $fullFileName,
                    $this->getNumberOfItemsInExportFile(),
                    $this->getAdditionalExportData()
                );
                $this->exportQueueRegistry->addToNewItemsCount($this->getNumberOfItemsInExportFile());
                $this->setNumberOfItemsInExportFile(0);
            }
        } catch (CouldNotSaveException $e) {
            $errorMessage = __(
                'An error occurred while adding entities to the export. Original Error Message: %1',
                $e->getMessage()
            );
            $initialExportStatus->setTotalErrorItems(
                $initialExportStatus->getTotalErrorItems() + $this->getNumberOfItemsInExportFile()
            );
            $this->addNewErrorMessage->execute($initialExportStatus, $errorMessage->render());
            $this->logger->error($errorMessage);
        } catch (FileSystemException $e) {
            $initialExportStatus->setTotalErrorItems(
                $initialExportStatus->getTotalErrorItems() + $size
            );
            $this->addNewErrorMessage->execute(
                $initialExportStatus,
                __(
                    'An error occurred while adding entities to the export. See log for a detailed error message.'
                )->render()
            );
            throw $e;
        } catch (Exception $e) {
            $this->addNewErrorMessage->execute(
                $initialExportStatus,
                __(
                    'An error occurred while adding entities to the export. See log for a detailed error message.'
                )->render()
            );
            throw $e;
        }

        return true;
    }

    /**
     * Maps items data
     *
     * @param string $entityType
     * @param array $items
     *
     * @return array
     */
    private function mapItems(string $entityType, array $items): array
    {
        $data = [];

        foreach ($items as $item) {
            $data[] = $this->dataMapperResolver->map($item, $entityType)->toArray();
        }

        return $data;
    }

    /**
     * Get total number
     *
     * @return int
     */
    private function getNumberOfItemsInExportFile(): int
    {
        return (int) $this->numberOfItemsInExportFile;
    }

    /**
     * Set total number
     *
     * @param int $numberOfItemsInExportFile
     *
     * @return void
     */
    private function setNumberOfItemsInExportFile(int $numberOfItemsInExportFile): void
    {
        $this->numberOfItemsInExportFile = $numberOfItemsInExportFile;
    }

    /**
     * Get file name
     *
     * @return string
     */
    private function getExportFileName(): string
    {
        return $this->exportFileName;
    }

    /**
     * Set export file name
     *
     * @param string $exportFileName
     *
     * @return void
     */
    private function setExportFileName(string $exportFileName): void
    {
        $this->exportFileName = $exportFileName;
    }

    /**
     * Reset additional export data
     *
     * @return void
     */
    private function resetAdditionalExportData(): void
    {
        $this->additionalExportData = [];
    }

    /**
     * Adds item to additional data
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    private function addItemToAdditionalExportData(string $key, string $value): void
    {
        $this->additionalExportData[$key] = $value;
    }

    /**
     * Returns additional export data
     *
     * @return array
     */
    private function getAdditionalExportData(): array
    {
        return $this->additionalExportData;
    }

    /**
     * Obtains entity id field name
     *
     * @param AbstractDb $collection
     *
     * @return string
     */
    private function getEntityIdFieldName(AbstractDb $collection): string
    {
        return (string) $collection->getResource()->getIdFieldName();
    }

    /**
     * Set filters
     *
     * @param AbstractDb $collection
     * @param int $lastLoadedItemId
     *
     * @return void
     */
    private function setFilters(AbstractDb $collection, int $lastLoadedItemId): void
    {
        $collection->addAttributeToSelect('*');
        $collection->setPageSize(self::PAGE_SIZE);

        if ($collection instanceof ProductCollection) {
            $collection->addGtThenItemIdFilter($lastLoadedItemId);
        } else {
            $collection->addFieldToFilter($this->getEntityIdFieldName($collection), ['gt' => $lastLoadedItemId]);
        }
    }

    /**
     * Get Last Loaded Item ID
     *
     * @param AbstractDb $collection
     *
     * @return int
     */
    private function getLastLoadedItemId(AbstractDb $collection): int
    {
        return $collection instanceof ProductCollection
            ? $collection->getLastLoadedItemId()
            : (int) $collection->getLastItem()->getData($this->getEntityIdFieldName($collection));
    }
}
