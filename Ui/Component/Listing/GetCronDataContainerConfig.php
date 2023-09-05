<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Ui\Component\Listing;

use Bloomreach\EngagementConnector\Api\Data\CronJobDataInterface;
use Bloomreach\EngagementConnector\Service\Cron\GetCronJobDataFromFlag;
use Bloomreach\EngagementConnector\Service\Cron\GetMinutesTimeDiff;
use Bloomreach\EngagementConnector\Service\Cron\GetNextPendingCronJobScheduledDate;

/**
 * Get config data for the Cron Data Container
 */
class GetCronDataContainerConfig
{
    /**
     * @var GetCronJobDataFromFlag
     */
    private $getCronJobDataFromFlag;

    /**
     * @var GetMinutesTimeDiff
     */
    private $getMinutesTimeDiff;

    /**
     * @var GetNextPendingCronJobScheduledDate
     */
    private $getNextPendingCronJobScheduledDate;

    /**
     * @param GetCronJobDataFromFlag $getCronJobDataFromFlag
     * @param GetMinutesTimeDiff $getMinutesTimeDiff
     * @param GetNextPendingCronJobScheduledDate $getNextPendingCronJobScheduledDate
     */
    public function __construct(
        GetCronJobDataFromFlag $getCronJobDataFromFlag,
        GetMinutesTimeDiff $getMinutesTimeDiff,
        GetNextPendingCronJobScheduledDate $getNextPendingCronJobScheduledDate
    ) {
        $this->getCronJobDataFromFlag = $getCronJobDataFromFlag;
        $this->getMinutesTimeDiff = $getMinutesTimeDiff;
        $this->getNextPendingCronJobScheduledDate = $getNextPendingCronJobScheduledDate;
    }

    /**
     *  Get config data for the Cron Data Container
     *
     * @param array $config
     * @param string $cronJobCode
     *
     * @return array
     */
    public function execute(array $config, string $cronJobCode): array
    {
        $cronData = $this->getCronJobDataFromFlag->execute($cronJobCode);
        $lastRunDate = $cronData[CronJobDataInterface::UPDATED_AT] ?? null;
        $itemsCount = $cronData[CronJobDataInterface::ITEM_COUNT] ?? null;
        $nextRunDate = $this->getNextPendingCronJobScheduledDate->execute($cronJobCode);

        // If no needed data - nothing to prepare
        if ($lastRunDate === null && $itemsCount === null && $nextRunDate === null) {
            return $config;
        }

        // Set visibility
        $config['visible'] = true;
        // Set last cron run diff in minutes
        $config['last_run_minutes'] = $lastRunDate !== null
            ? $this->getMinutesTimeDiff->execute((string)$lastRunDate)
            : null;
        // Set last cron processed items count
        $config['items_count'] = $itemsCount;
        // Set future cron run diff in minutes
        $config['next_run_minutes'] = $nextRunDate !== null
            ? $this->getMinutesTimeDiff->execute($nextRunDate)
            : null;

        return $config;
    }
}
