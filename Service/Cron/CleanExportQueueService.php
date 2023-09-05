<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

use Bloomreach\EngagementConnector\Api\DeleteExportQueueInterface;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

/**
 * Clean export queue data from DB
 */
class CleanExportQueueService
{
    private const DAYS_CLEAN_EXPORT_QUEUE =
        'bloomreach_engagement/bloomreach_engagement_cron/clean_export_queue';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DeleteExportQueueInterface
     */
    private $deleteExportQueue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $date
     * @param CollectionFactory $collectionFactory
     * @param DeleteExportQueueInterface $deleteExportQueue
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $date,
        CollectionFactory $collectionFactory,
        DeleteExportQueueInterface $deleteExportQueue,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->collectionFactory = $collectionFactory;
        $this->deleteExportQueue = $deleteExportQueue;
        $this->logger = $logger;
    }

    /**
     * Clean export queue data from DB
     */
    public function execute(): void
    {
        $day = $this->scopeConfig->getValue(self::DAYS_CLEAN_EXPORT_QUEUE);

        if ($day > 0) {
            $timeEnd = strtotime($this->date->date()) - $day * 24 * 60 * 60;
            $lastEntityId = 0;

            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $this->setFilters($collection, $timeEnd, $lastEntityId);

            $collectionSize = $collection->getSize();

            if (!$collectionSize) {
                return;
            }

            $collection->setPageSize(100);
            $lastPageNumber = $collection->getLastPageNumber();

            for ($currentPage = 1; $currentPage <= $lastPageNumber; $currentPage++) {
                $collection->getSelect()->reset('where');
                $this->setFilters($collection, $timeEnd, $lastEntityId);
                $lastEntityId = $this->deleteQueue($collection);
            }
        }
    }

    /**
     * Deletes old queue
     *
     * @param Collection $collection
     *
     * @return int
     */
    private function deleteQueue(Collection $collection): int
    {
        $lastEntityId = 0;

        /** @var ExportQueueModel $item */
        foreach ($collection as $item) {
            $lastEntityId = $item->getEntityId();
            try {
                $this->deleteExportQueue->execute($item->getEntityId());
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }

        $collection->clear();

        return (int) $lastEntityId;
    }

    /**
     * Get Collection with filters
     *
     * @param Collection $collection
     * @param int $timeEnd
     * @param int $lastEntityId
     *
     * @return void
     */
    private function setFilters(Collection $collection, int $timeEnd, int $lastEntityId): void
    {
        $collection
            ->addFieldToFilter(ExportQueueModel::CREATED_AT, ['lteq' => date('Y-m-d H:i:s', $timeEnd)])
            ->addFieldToFilter(ExportQueueModel::STATUS, ['eq' => ExportQueueModel::STATUS_COMPLETE])
            ->addFieldToFilter(ExportQueueModel::ENTITY_ID, ['gt' => $lastEntityId]);
    }
}
