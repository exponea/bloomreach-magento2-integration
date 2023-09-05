<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Condition;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;

/**
 * The class is responsible for checking if allowed to export item
 */
class IsExportingItemAllowed
{
    /**
     * @var IsInitialExportAllowed
     */
    private $isInitialExportAllowed;

    /**
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @param IsInitialExportAllowed $isInitialExportAllowed
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     */
    public function __construct(
        IsInitialExportAllowed $isInitialExportAllowed,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
    ) {
        $this->isInitialExportAllowed = $isInitialExportAllowed;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
    }

    /**
     * Checks if export allowed for item
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     */
    public function execute(ExportQueueInterface $exportQueue): bool
    {
        if ($exportQueue->getApiType() === AddInitialExportDataToExportQueue::API_TYPE) {
            return $this->isInitialExportAllowed->execute($exportQueue->getEntityType());
        }

        return $this->isRealTimeUpdateAllowed->execute($exportQueue->getEntityType());
    }
}
