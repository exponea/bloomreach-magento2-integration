<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection as ExportQueueCollection;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemsGetter;

/**
 * The class is responsible for adding an API type filter to first process the initial export records
 */
class AddApiTypeFilter
{
    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var ItemsGetter
     */
    private $itemsGetter;

    /**
     * @param EntityType $entityType
     * @param ItemsGetter $itemsGetter
     */
    public function __construct(
        EntityType $entityType,
        ItemsGetter $itemsGetter
    ) {
        $this->entityType = $entityType;
        $this->itemsGetter = $itemsGetter;
    }

    /**
     * Adds api type filter
     *
     * @param ExportQueueCollection $exportQueueCollection
     *
     * @return void
     */
    public function execute(ExportQueueCollection $exportQueueCollection): void
    {
        $result[] = sprintf(
            '(%s NOT IN (%s))',
            ExportQueueModel::ENTITY_TYPE,
            implode(',', array_map([$this, 'addQuotes'], $this->entityType->getAllTypes()))
        );

        foreach ($this->itemsGetter->execute() as $item) {
            if ($item->isLocked()) {
                $result[] = sprintf(
                    '(%s !=\'%s\')',
                    ExportQueueModel::ENTITY_TYPE,
                    $item->getEntityType()
                );
            } elseif (in_array($item->getStatus(), StatusSource::FINISHED_STATUSES)) {
                $result[] = sprintf(
                    '(%s = \'%s\' AND %s != \'%s\')',
                    ExportQueueModel::ENTITY_TYPE,
                    $item->getEntityType(),
                    ExportQueueModel::API_TYPE,
                    AddInitialExportDataToExportQueue::API_TYPE
                );
            } elseif (in_array($item->getStatus(), StatusSource::IN_PROGRESS_STATUSES)) {
                $result[] = sprintf(
                    '(%s = \'%s\' AND %s = \'%s\')',
                    ExportQueueModel::ENTITY_TYPE,
                    $item->getEntityType(),
                    ExportQueueModel::API_TYPE,
                    AddInitialExportDataToExportQueue::API_TYPE
                );
            }
        }

        $exportQueueCollection->getSelect()->where(implode(' OR ', $result));
    }

    /**
     * Adds quotes
     *
     * @param string $item
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function addQuotes(string $item): string
    {
        return sprintf('\'%s\'', $item);
    }
}
