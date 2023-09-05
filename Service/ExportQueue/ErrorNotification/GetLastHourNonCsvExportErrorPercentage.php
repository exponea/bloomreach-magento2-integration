<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportQueue\ErrorNotification;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResource;
use Magento\Framework\Exception\LocalizedException;

/**
 * Get last hour error percentage for the export queue items except csv export related items
 */
class GetLastHourNonCsvExportErrorPercentage
{
    /**
     * @var ExportQueueResource
     */
    private $exportQueueResource;

    /**
     * @param ExportQueueResource $exportQueueResource
     */
    public function __construct(ExportQueueResource $exportQueueResource)
    {
        $this->exportQueueResource = $exportQueueResource;
    }

    /**
     * Get last hour error percentage for the export queue items except csv export related items
     *
     * @return int
     * @throws LocalizedException
     */
    public function execute(): int
    {
        $errorCounter = $otherCounter = 0;
        $statusCountList = $this->exportQueueResource->getLastHourNonCsvItemsStatusCountList();
        foreach ($statusCountList as $status => $statusCount) {
            if ($status === ExportQueueModel::STATUS_ERROR) {
                $errorCounter = $statusCount;
            } else {
                $otherCounter += $statusCount;
            }
        }

        if ($errorCounter === 0) {
            return $errorCounter;
        }

        return (int)number_format($errorCounter / ($errorCounter + $otherCounter) * 100);
    }
}
