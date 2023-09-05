<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Cron;

use Bloomreach\EngagementConnector\Service\ExportQueue\SendErrorNotification;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;

/**
 * Send export error notification
 */
class ExportQueueErrorNotificationRunner
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SendErrorNotification
     */
    private $sendErrorNotification;

    /**
     * @param ConfigProvider $configProvider
     * @param SendErrorNotification $sendErrorNotification
     */
    public function __construct(
        ConfigProvider $configProvider,
        SendErrorNotification $sendErrorNotification
    ) {
        $this->configProvider = $configProvider;
        $this->sendErrorNotification = $sendErrorNotification;
    }

    /**
     * Send export error notification
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if ($this->configProvider->isEnabled()) {
            $this->sendErrorNotification->execute();
        }
    }
}
