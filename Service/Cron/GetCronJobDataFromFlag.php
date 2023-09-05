<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Cron;

use Magento\Framework\FlagManager;

/**
 * Get cron data from the `flag` table
 */
class GetCronJobDataFromFlag
{
    /**
     * @var FlagManager
     */
    private $flagManager;

    /**
     * @param FlagManager $flagManager
     */
    public function __construct(FlagManager $flagManager)
    {
        $this->flagManager = $flagManager;
    }

    /**
     * Get cron data from the `flag` table
     *
     * @param string $cronJobDataFlagCode
     *
     * @return array
     */
    public function execute(string $cronJobDataFlagCode): array
    {
        $cronData = $this->flagManager->getFlagData($cronJobDataFlagCode);

        return is_array($cronData) ? $cronData : [];
    }
}
