<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Cron;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Model\Export\ExportProcessor;

/**
 * Runs cron job for export data to the Bloomreach
 */
class ExportRunner
{
    /**
     * @var ExportProcessor
     */
    private $exportProcessor;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param ExportProcessor $exportProcessor
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        ExportProcessor $exportProcessor,
        ConfigProvider $configProvider
    ) {
        $this->exportProcessor = $exportProcessor;
        $this->configProvider = $configProvider;
    }

    /**
     * Runs cron job for export data to the Bloomreach
     */
    public function execute(): void
    {
        if ($this->configProvider->isEnabled()) {
            $this->exportProcessor->process();
        }
    }
}
