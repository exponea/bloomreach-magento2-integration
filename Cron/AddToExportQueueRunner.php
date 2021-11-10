<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Cron;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Model\Export\QueueProcessor;

/**
 * Run cron job for adding entity types to the export queue
 */
class AddToExportQueueRunner
{
    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param QueueProcessor $queueProcessor
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        QueueProcessor $queueProcessor,
        ConfigProvider $configProvider
    ) {
        $this->queueProcessor = $queueProcessor;
        $this->configProvider = $configProvider;
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
        }
    }
}
