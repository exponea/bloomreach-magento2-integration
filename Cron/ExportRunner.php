<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Cron;

use Bloomreach\EngagementConnector\Model\Export\ExportProcessor;
use Bloomreach\EngagementConnector\Registry\RunQueue as RunQueueRegistry;
use Bloomreach\EngagementConnector\Service\Cron\SaveCronJobDataToFlag;
use Bloomreach\EngagementConnector\System\ConfigProvider;

/**
 * Runs cron job for export data to the Bloomreach
 */
class ExportRunner
{
    /**
     * Related cron job name
     */
    public const CRON_JOB_CODE = 'bloomreach_run_export';

    /**
     * @var ExportProcessor
     */
    private $exportProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RunQueueRegistry
     */
    private $runQueueRegistry;

    /**
     * @var SaveCronJobDataToFlag
     */
    private $saveCronJobDataToFlag;

    /**
     * @param ExportProcessor $exportProcessor
     * @param ConfigProvider $configProvider
     * @param RunQueueRegistry $runQueueRegistry
     * @param SaveCronJobDataToFlag $saveCronJobDataToFlag
     */
    public function __construct(
        ExportProcessor $exportProcessor,
        ConfigProvider $configProvider,
        RunQueueRegistry $runQueueRegistry,
        SaveCronJobDataToFlag $saveCronJobDataToFlag
    ) {
        $this->exportProcessor = $exportProcessor;
        $this->configProvider = $configProvider;
        $this->runQueueRegistry = $runQueueRegistry;
        $this->saveCronJobDataToFlag = $saveCronJobDataToFlag;
    }

    /**
     * Runs cron job for export data to the Bloomreach
     */
    public function execute(): void
    {
        if ($this->configProvider->isEnabled()) {
            $this->exportProcessor->process();
            $this->saveCronJobDataToFlag->execute(
                self::CRON_JOB_CODE,
                $this->runQueueRegistry->getNewItemsCount()
            );
        }
    }
}
