<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;

/**
 * The class is responsible for obtaining data from export queue and send them to the Bloomreach service
 */
class ExportProcessor
{
    private const PAGE_SIZE = 100;

    private const MAX_RETRIES = 5;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SenderProcessor
     */
    private $senderProcessor;

    /**
     * @param CollectionFactory $collectionFactory
     * @param SenderProcessor $senderProcessor
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        SenderProcessor $senderProcessor
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->senderProcessor = $senderProcessor;
    }

    /**
     * Processes of sending export queue items to the Bloomreach service
     *
     * @return void
     */
    public function process(): void
    {
        $collection = $this->getExportQueueCollection();
        $collectionSize = $collection->getSize();

        if (!$collectionSize) {
            return;
        }

        $collection->setPageSize(self::PAGE_SIZE);
        $lastPageNumber = $collection->getLastPageNumber();

        for ($currentPage = $lastPageNumber; $currentPage >= 1; $currentPage--) {
            $collection->setCurPage($currentPage);
            $this->send($collection);
        }
    }

    /**
     * Returns collection of the export queue items
     *
     * @return Collection
     */
    private function getExportQueueCollection(): Collection
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
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

        return $collection;
    }

    /**
     * Send items to the Bloomreach
     *
     * @param Collection $collection
     *
     * @return void
     */
    private function send(Collection $collection): void
    {
        /** @var ExportQueueInterface $item */
        foreach ($collection as $item) {
            $this->senderProcessor->process($item);
        }

        $collection->clear();
    }
}
