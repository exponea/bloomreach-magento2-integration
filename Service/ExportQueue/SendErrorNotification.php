<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportQueue;

use Bloomreach\EngagementConnector\Service\ExportQueue\ErrorNotification\GetLastHourNonCsvExportErrorPercentage;
use Bloomreach\EngagementConnector\Service\ExportQueue\ErrorNotification\SendNotificationEmail;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;

/**
 * Send error notification email if needed
 */
class SendErrorNotification
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetLastHourNonCsvExportErrorPercentage
     */
    private $getLastHourNonCsvExportErrorPercentage;

    /**
     * @var SendNotificationEmail
     */
    private $sendNotificationEmail;

    /**
     * @param ConfigProvider $configProvider
     * @param GetLastHourNonCsvExportErrorPercentage $getLastHourNonCsvExportErrorPercentage
     * @param SendNotificationEmail $sendNotificationEmail
     */
    public function __construct(
        ConfigProvider $configProvider,
        GetLastHourNonCsvExportErrorPercentage $getLastHourNonCsvExportErrorPercentage,
        SendNotificationEmail $sendNotificationEmail
    ) {
        $this->configProvider = $configProvider;
        $this->getLastHourNonCsvExportErrorPercentage = $getLastHourNonCsvExportErrorPercentage;
        $this->sendNotificationEmail = $sendNotificationEmail;
    }

    /**
     * Send error notification if needed:
     *  - If feature is enabled.
     *  - Get & prepare needed data: sender, recipients, allowed error percentage.
     *  - Get current error percentage & compare with allowed one.
     *  - Send error notification email if needed.
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        if (!$this->configProvider->isNotificationEnabled()) {
            return;
        }
        $sender = $this->getSender();
        $recipients = $this->getRecipients();

        // Get percentage of non csv related export queue errors in the last hour
        $errorsPercentage = $this->getLastHourNonCsvExportErrorPercentage->execute();
        if ($errorsPercentage <= $this->getAllowedErrorPercentage()) {
            return;
        }

        // Send email notification
        $this->sendNotificationEmail->execute($sender, $recipients, $errorsPercentage);
    }

    /**
     * Get allowed error percentage, returns 0 if provided value is not in the range 0..100
     *
     * @return int
     */
    private function getAllowedErrorPercentage(): int
    {
        $allowedErrorPercentage = $this->configProvider->getNotificationAllowedErrorPercentage();

        return ($allowedErrorPercentage < 0 || $allowedErrorPercentage > 100) ? 0 : $allowedErrorPercentage;
    }

    /**
     * Get sender
     *
     * @return string
     * @throws LocalizedException
     */
    private function getSender(): string
    {
        $sender = $this->configProvider->getNotificationSender();
        if ($sender === '') {
            throw new LocalizedException(__('The sender should be specified'));
        }

        return $sender;
    }

    /**
     * Get recipient list
     *
     * @return array
     * @throws LocalizedException
     */
    private function getRecipients(): array
    {
        $recipientList = [];
        $recipients = $this->configProvider->getNotificationRecipients();
        if ($recipients === '') {
            throw new LocalizedException(__('The recipient list can\'t be empty'));
        }

        $recipients = explode(',', $recipients);
        foreach ($recipients as $recipient) {
            $recipient = trim($recipient);
            if ($recipient !== '') {
                $recipientList[] = $recipient;
            }
        }

        if ($recipientList === []) {
            throw new LocalizedException(__('The recipient list can\'t be empty'));
        }

        return $recipientList;
    }
}
