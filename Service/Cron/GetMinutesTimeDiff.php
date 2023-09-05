<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Get time diff between provided and the current time in minutes, min value - 1 minute.
 */
class GetMinutesTimeDiff
{
    /**
     * @var TimezoneInterface
     */
    private $timeZone;

    /**
     * @param TimezoneInterface $timeZone
     */
    public function __construct(TimezoneInterface $timeZone)
    {
        $this->timeZone = $timeZone;
    }

    /**
     * Get time diff between provided and the current time in minutes, min value - 1 minute.
     *
     * @param string $date
     *
     * @return int
     */
    public function execute(string $date): int
    {
        $date = $this->timeZone->date($date);
        $nowDate = $this->timeZone->date();
        $diffInSeconds = $nowDate->getTimestamp() - $date->getTimestamp();
        $diffInMinutes = (int)abs($diffInSeconds / 60);

        return max(1, $diffInMinutes);
    }
}
