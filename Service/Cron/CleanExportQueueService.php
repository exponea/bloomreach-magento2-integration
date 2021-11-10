<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $date
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        DateTime $date,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->collectionFactory = $collectionFactory;
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

            /** @var Collection $collection */
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('created_at', ['lteq' => date('Y-m-d H:i:s', $timeEnd)])
                ->addFieldToFilter('status', ['eq' => ExportQueueModel::STATUS_COMPLETE]);

            $collectionSize = $collection->getSize();

            if (!$collectionSize) {
                return;
            }

            $collection->setPageSize(100);
            $lastPageNumber = $collection->getLastPageNumber();

            for ($currentPage = $lastPageNumber; $currentPage >= 1; $currentPage--) {
                $collection->setCurPage($currentPage);
                $this->deleteQueue($collection);
            }
        }
    }

    /**
     * Deletes old queue
     *
     * @param Collection $collection
     *
     * @return void
     */
    private function deleteQueue(Collection $collection): void
    {
        foreach ($collection as $item) {
            try {
                $item->delete();
            } catch (Exception $e) {
                $this->logger->critical($e);
            }
        }

        $collection->clear();
    }
}
