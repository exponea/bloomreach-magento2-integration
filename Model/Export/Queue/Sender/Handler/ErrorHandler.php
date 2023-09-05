<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Sender\Handler;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Service\ExportQueue\CalculateTimeOfNextSendingAttempt;

/**
 * The class is responsible for handling error sending result
 */
class ErrorHandler
{
    /**
     * @var CalculateTimeOfNextSendingAttempt
     */
    private $calculateTimeOfNextSendingAttempt;

    /**
     * @param CalculateTimeOfNextSendingAttempt $calculateTimeOfNextSendingAttempt
     */
    public function __construct(CalculateTimeOfNextSendingAttempt $calculateTimeOfNextSendingAttempt)
    {
        $this->calculateTimeOfNextSendingAttempt = $calculateTimeOfNextSendingAttempt;
    }

    /**
     * Handles sending error
     *
     * @param ExportQueueInterface $exportQueue
     * @param string $errorMessage
     *
     * @return void
     */
    public function handle(ExportQueueInterface $exportQueue, string $errorMessage): void
    {
        $exportQueue->setStatus(ExportQueueModel::STATUS_ERROR);
        $failedAttempts = $exportQueue->getFailedSendingAttempts() + 1;
        $exportQueue->setTimeOfNextSendingAttempt($this->calculateTimeOfNextSendingAttempt->execute($failedAttempts));
        $exportQueue->setFailedSendingAttempts($failedAttempts);
        $exportQueue->setErrorMessage($errorMessage);
    }
}
