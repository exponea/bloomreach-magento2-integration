<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Cron;

use Bloomreach\EngagementConnector\Service\Cron\CleanExportQueueService;

/**
 * Runs cron job for clean export queue data from DB
 */
class CleanExportQueueRunner
{
    /**
     * @var CleanExportQueueService
     */
    private $cleanExportQueueService;

    /**
     * @param CleanExportQueueService $cleanExportQueueService
     */
    public function __construct(CleanExportQueueService $cleanExportQueueService)
    {
        $this->cleanExportQueueService = $cleanExportQueueService;
    }

    /**
     * Runs cron job for clean export queue data from DB
     */
    public function execute(): void
    {
        $this->cleanExportQueueService->execute();
    }
}
