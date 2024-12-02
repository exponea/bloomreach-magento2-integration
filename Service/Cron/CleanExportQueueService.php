<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

use Bloomreach\EngagementConnector\Api\DeleteExportQueueInterface;
use Bloomreach\EngagementConnector\Logger\Debugger;
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
    private const TOTAL_ERRORS = 'total_errors';
    private const TOTAL_STATUS_COMPLETE = 'total_status_complete';
    private const TOTAL_STATUS_ERROR = 'total_status_error';
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
     * @var Debugger
     */
    private $debugLogger;

    /**
     * @var array
     */
    private $cleanUpResult = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $date
     * @param CollectionFactory $collectionFactory
     * @param DeleteExportQueueInterface $deleteExportQueue
     * @param LoggerInterface $logger
     * @param Debugger $debuggerLogger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $date,
        CollectionFactory $collectionFactory,
        DeleteExportQueueInterface $deleteExportQueue,
        LoggerInterface $logger,
        Debugger $debuggerLogger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->collectionFactory = $collectionFactory;
        $this->deleteExportQueue = $deleteExportQueue;
        $this->logger = $logger;
        $this->debugLogger = $debuggerLogger;
    }

    /**
     * Clean export queue data from DB
     */
    public function execute(): void
    {
        $day = $this->scopeConfig->getValue(self::DAYS_CLEAN_EXPORT_QUEUE);

        if (!$day) {
            $this->logger->info(__('Export queue cleanup is not configured'));

            return;
        }

        $this->debugLogger->log(__('Export queue cleanup started'));
        $timeEnd = strtotime($this->date->date()) - $day * 24 * 60 * 60;
        $lastEntityId = 0;

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->setFilters($collection, $timeEnd, $lastEntityId);
        $collectionSize = $collection->getSize();

        if (!$collectionSize) {

            $this->debugLogger->log(__('Export queue cleanup complete. No records were deleted'));
            return;
        }

        $collection->setPageSize(100);
        $lastPageNumber = $collection->getLastPageNumber();

        for ($currentPage = 1; $currentPage <= $lastPageNumber; $currentPage++) {
            $collection->getSelect()->reset('where');
            $this->setFilters($collection, $timeEnd, $lastEntityId);
            $lastEntityId = $this->deleteQueue($collection);
        }

        $this->debugLogger->log(
            __(
                'Export queue cleanup complete.'
                    . ' Total records deleted with status "complete": "%total_complete_status".'
                    . ' Total records deleted with status "error": "%total_error_status".'
                    . ' Total errors encountered: "%total_errors"',
                [
                    'total_complete_status' => $this->cleanUpResult[self::TOTAL_STATUS_COMPLETE] ?? 0,
                    'total_error_status' => $this->cleanUpResult[self::TOTAL_STATUS_ERROR] ?? 0,
                    'total_errors' => $this->cleanUpResult[self::TOTAL_ERRORS] ?? 0
                ]
            )
        );
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
            $status = $item->getStatus();

            try {
                $this->deleteExportQueue->execute($item->getEntityId());
                $this->addDeletedToCleanupResult($status);
            } catch (Exception $e) {
                $this->logger->critical($e);
                $this->addErrorToCleanupResult();
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
            ->addFieldToFilter(
                ExportQueueModel::STATUS,
                ['in' => [ExportQueueModel::STATUS_COMPLETE, ExportQueueModel::STATUS_ERROR]]
            )
            ->addFieldToFilter(ExportQueueModel::ENTITY_ID, ['gt' => $lastEntityId]);
    }

    /**
     * Add 1 error to cleanup results
     *
     * @return void
     */
    private function addErrorToCleanupResult(): void
    {
        $totalErrors = $this->cleanUpResult[self::TOTAL_ERRORS] ?? 0;
        $this->cleanUpResult[self::TOTAL_ERRORS] = $totalErrors + 1;
    }

    /**
     * Add 1 deleted to cleanup results
     *
     * @param int $status
     *
     * @return void
     */
    private function addDeletedToCleanupResult(int $status): void
    {
        $key = self::TOTAL_STATUS_ERROR;

        if ($status === ExportQueueModel::STATUS_COMPLETE) {
            $key = self::TOTAL_STATUS_COMPLETE;
        }

        $value = $this->cleanUpResult[$key] ?? 0;
        $this->cleanUpResult[$key] = $value + 1;
    }
}
