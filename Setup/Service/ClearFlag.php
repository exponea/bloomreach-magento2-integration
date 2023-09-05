<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup\Service;

use Bloomreach\EngagementConnector\Cron\AddToExportQueueRunner;
use Bloomreach\EngagementConnector\Cron\ExportRunner;
use Magento\Framework\FlagManager;

/**
 * Clear flag table: remove module related data from the `flag` table
 */
class ClearFlag
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
     * Clear flag table: remove module related data from the `flag` table
     *
     * @return void
     */
    public function execute(): void
    {
        $this->flagManager->deleteFlag(AddToExportQueueRunner::CRON_JOB_CODE);
        $this->flagManager->deleteFlag(ExportRunner::CRON_JOB_CODE);
    }
}
