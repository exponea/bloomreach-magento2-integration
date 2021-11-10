<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Cron;

use Bloomreach\EngagementConnector\Service\Cron\CleanCsvFilesService;

/**
 * Runs cron job for clean export csv files
 */
class CleanCsvRunner
{
    /**
     * @var CleanCsvFilesService
     */
    private $cleanCsvFilesService;

    /**
     * @param CleanCsvFilesService $cleanCsvFilesService
     */
    public function __construct(CleanCsvFilesService $cleanCsvFilesService)
    {
        $this->cleanCsvFilesService = $cleanCsvFilesService;
    }

    /**
     * Runs cron job for clean export csv files
     */
    public function execute(): void
    {
        $this->cleanCsvFilesService->execute();
    }
}
