<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Cron;

use Bloomreach\EngagementConnector\Model\Export\QueueProcessor;
use Bloomreach\EngagementConnector\Registry\ExportQueue as ExportQueueRegistry;
use Bloomreach\EngagementConnector\Service\Cron\SaveCronJobDataToFlag;
use Bloomreach\EngagementConnector\System\ConfigProvider;

/**
 * Run cron job for adding entity types to the export queue
 */
class AddToExportQueueRunner
{
    /**
     * Related cron job name
     */
    public const CRON_JOB_CODE = 'bloomreach_add_to_export_queue';

    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var ExportQueueRegistry
     */
    private $exportQueueRegistry;

    /**
     * @var SaveCronJobDataToFlag
     */
    private $saveCronJobDataToFlag;

    /**
     * @param QueueProcessor $queueProcessor
     * @param ConfigProvider $configProvider
     * @param ExportQueueRegistry $exportQueueRegistry
     * @param SaveCronJobDataToFlag $saveCronJobDataToFlag
     */
    public function __construct(
        QueueProcessor $queueProcessor,
        ConfigProvider $configProvider,
        ExportQueueRegistry $exportQueueRegistry,
        SaveCronJobDataToFlag $saveCronJobDataToFlag
    ) {
        $this->queueProcessor = $queueProcessor;
        $this->configProvider = $configProvider;
        $this->exportQueueRegistry = $exportQueueRegistry;
        $this->saveCronJobDataToFlag = $saveCronJobDataToFlag;
    }

    /**
     * Run cron job for adding entity types to the export queue
     *
     * @return void
     */
    public function execute(): void
    {
        if ($this->configProvider->isEnabled()) {
            $this->queueProcessor->process();
            $this->saveCronJobDataToFlag->execute(
                self::CRON_JOB_CODE,
                $this->exportQueueRegistry->getNewItemsCount()
            );
        }
    }
}
