<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsExportingItemAllowed;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddApiTypeFilter;
use Bloomreach\EngagementConnector\Model\Export\Queue\Batch\IsBatchApiItem;
use Bloomreach\EngagementConnector\Model\Export\Queue\Sender\SenderBatchProcessor;
use Bloomreach\EngagementConnector\Model\Export\Queue\Sender\SenderProcessor;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;

/**
 * The class is responsible for obtaining data from export queue and send them to the Bloomreach service
 */
class ExportProcessor
{
    public const MAX_RETRIES = 5;

    private const PAGE_SIZE = 100;

    private const MAX_ITEMS_IN_BATCH = 5;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SenderProcessor
     */
    private $senderProcessor;

    /**
     * @var IsExportingItemAllowed
     */
    private $isExportingItemAllowed;

    /**
     * @var AddApiTypeFilter
     */
    private $addApiTypeFilter;

    /**
     * @var SenderBatchProcessor
     */
    private $senderBatchProcessor;

    /**
     * @var IsBatchApiItem
     */
    private $isBatchApiItem;

    /**
     * @var array
     */
    private $batchApiItems = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param SenderProcessor $senderProcessor
     * @param IsExportingItemAllowed $isExportingItemAllowed
     * @param AddApiTypeFilter $addApiTypeFilter
     * @param SenderBatchProcessor $senderBatchProcessor
     * @param IsBatchApiItem $isBatchApiItem
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SenderProcessor $senderProcessor,
        IsExportingItemAllowed $isExportingItemAllowed,
        AddApiTypeFilter $addApiTypeFilter,
        SenderBatchProcessor $senderBatchProcessor,
        IsBatchApiItem $isBatchApiItem
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->senderProcessor = $senderProcessor;
        $this->isExportingItemAllowed = $isExportingItemAllowed;
        $this->addApiTypeFilter = $addApiTypeFilter;
        $this->senderBatchProcessor = $senderBatchProcessor;
        $this->isBatchApiItem = $isBatchApiItem;
    }

    /**
     * Processes of sending export queue items to the Bloomreach service
     *
     * @return void
     */
    public function process(): void
    {
        $collection = $this->collectionFactory->create();
        $this->setFilters($collection);
        $collectionSize = $collection->getSize();

        if (!$collectionSize) {
            return;
        }

        $collection->setPageSize(self::PAGE_SIZE);
        $lastPageNumber = $collection->getLastPageNumber();
        $lastEntityId = 0;

        for ($currentPage = 1; $currentPage <= $lastPageNumber; $currentPage++) {
            $collection->getSelect()->reset('where');
            $collection->addFieldToFilter(ExportQueueModel::ENTITY_ID, ['gt' => $lastEntityId]);
            $this->setFilters($collection);
            $lastEntityId = $this->send($collection);
        }
    }

    /**
     * Set filters
     *
     * @param Collection $collection
     *
     * @return void
     */
    private function setFilters(Collection $collection): void
    {
        $collection->addFieldToFilter(
            ExportQueueModel::STATUS,
            [
                'in' => [
                    ExportQueueModel::STATUS_NEW,
                    ExportQueueModel::STATUS_ERROR
                ]
            ]
        );

        $collection->addFieldToFilter(
            ExportQueueModel::RETRIES,
            [
                'lt' => self::MAX_RETRIES
            ]
        );

        $collection->addFieldToFilter(
            ExportQueueModel::TIME_OF_NEXT_SENDING_ATTEMPT,
            [
                'lteq' => time()
            ]
        );

        //First process the initial export records
        $this->addApiTypeFilter->execute($collection);
    }

    /**
     * Send items to the Bloomreach
     *
     * @param Collection $collection
     *
     * @return int
     */
    private function send(Collection $collection): int
    {
        $lastEntityId = 0;

        /** @var ExportQueueInterface $item */
        foreach ($collection as $item) {
            $lastEntityId = $item->getEntityId();

            if (!$this->isExportingItemAllowed->execute($item)) {
                continue;
            }

            if (count($this->batchApiItems) === self::MAX_ITEMS_IN_BATCH) {
                $this->sendBatch();
            }

            if ($this->isBatchApiItem->execute($item)) {
                $this->batchApiItems[] = $item;
                continue;
            }

            $this->sendBatch();
            $this->senderProcessor->process($item);
        }

        $this->sendBatch();
        $collection->clear();

        return (int) $lastEntityId;
    }

    /**
     * Sends Batch
     *
     * @return void
     */
    private function sendBatch(): void
    {
        if ($this->batchApiItems) {
            $this->senderBatchProcessor->process($this->batchApiItems);
        }

        $this->batchApiItems = [];
    }
}
