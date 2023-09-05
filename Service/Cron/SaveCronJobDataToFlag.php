<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

use Bloomreach\EngagementConnector\Api\Data\CronJobDataInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Save cron data (new items count and current date) to the `flag` table
 */
class SaveCronJobDataToFlag
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @param DateTime $dateTime
     * @param FlagManager $flagManager
     */
    public function __construct(
        DateTime $dateTime,
        FlagManager $flagManager
    ) {
        $this->dateTime = $dateTime;
        $this->flagManager = $flagManager;
    }

    /**
     * Save cron data (new items count and current date) to the `flag` table
     *
     * @param string $cronJobDataFlagCode
     * @param int $itemsCount
     *
     * @return void
     */
    public function execute(string $cronJobDataFlagCode, int $itemsCount): void
    {
        $this->flagManager->saveFlag(
            $cronJobDataFlagCode,
            [
                CronJobDataInterface::ITEM_COUNT => $itemsCount,
                CronJobDataInterface::UPDATED_AT => $this->dateTime->gmtDate(),
            ]
        );
    }
}
