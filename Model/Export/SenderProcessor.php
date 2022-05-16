<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Bloomreach\EngagementConnector\Exception\ExportRequestException;
use Bloomreach\EngagementConnector\Logger\Debugger;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Service\ExportQueue\CalculateTimeOfNextSendingAttempt;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for processing export queue item sending to the Bloomreach service
 */
class SenderProcessor
{
    /**
     * @var SaveExportQueueInterface
     */
    private $saveExportQueue;

    /**
     * @var TransporterInterface
     */
    private $transporterResolver;

    /**
     * @var CalculateTimeOfNextSendingAttempt
     */
    private $calculateTimeOfNextSendingAttempt;

    /**
     * @var Debugger
     */
    private $debugger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SaveExportQueueInterface $saveExportQueue
     * @param TransporterInterface $transporterResolver
     * @param CalculateTimeOfNextSendingAttempt $calculateTimeOfNextSendingAttempt
     * @param Debugger $debugger
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveExportQueueInterface $saveExportQueue,
        TransporterInterface $transporterResolver,
        CalculateTimeOfNextSendingAttempt $calculateTimeOfNextSendingAttempt,
        Debugger $debugger,
        LoggerInterface $logger
    ) {
        $this->saveExportQueue = $saveExportQueue;
        $this->transporterResolver = $transporterResolver;
        $this->calculateTimeOfNextSendingAttempt = $calculateTimeOfNextSendingAttempt;
        $this->debugger = $debugger;
        $this->logger = $logger;
    }

    /**
     * Processing export queue item sending to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return void
     */
    public function process(ExportQueueInterface $exportQueue): void
    {
        try {
            $updateRetries = true;
            $exportQueue->setStatus(ExportQueueModel::STATUS_IN_PROGRESS);
            $this->saveExportQueue->execute($exportQueue);
            $this->debugger->log(
                __(
                    'Export data for Export Queue with ID: %1 started.',
                    $exportQueue->getEntityId()
                )
            );
            $this->debugger->log(
                __('Export Queue ID: %1', $exportQueue->getEntityId())
            );
            $this->debugger->log(
                __('Entity Type: %1', $exportQueue->getEntityType())
            );
            $this->debugger->log(
                __('Api Type: %1', $exportQueue->getApiType())
            );
            $this->transporterResolver->send($exportQueue);
            $this->saveExportQueue($exportQueue);
            $exportQueue->setStatus(ExportQueueModel::STATUS_COMPLETE);
            $this->debugger->log(
                __(
                    'Export data for Export Queue with ID: %1 completed successfully.',
                    $exportQueue->getEntityId()
                )
            );
        } catch (ExportRequestException $e) {
            $updateRetries = $e->isNeedUpdateRetryCounter();
            $this->handleError($exportQueue, $e->getMessage());
        } catch (Exception $e) {
            $this->handleError($exportQueue, $e->getMessage());
        }

        $this->saveExportQueue($exportQueue, $updateRetries);
    }

    /**
     * Save export queue
     *
     * @param ExportQueueInterface $exportQueue
     * @param bool $updateRetries
     *
     * @return void
     *
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    private function saveExportQueue(ExportQueueInterface $exportQueue, bool $updateRetries = false): void
    {
        if ($updateRetries) {
            $exportQueue->setRetries($exportQueue->getRetries() + 1);
        }

        try {
            $this->saveExportQueue->execute($exportQueue);
        } catch (CouldNotSaveException $e) {
            $this->logger->error(
                __(
                    'An error occurred while saving the Export Queue ID: %1. ' .
                    'Entity type: %2.  Error: %3',
                    $exportQueue->getEntityId(),
                    $exportQueue->getEntityType(),
                    $e->getMessage(),
                )
            );
        }
    }

    /**
     * Handle error
     *
     * @param ExportQueueInterface $exportQueue
     * @param string $errorMessage
     *
     * @return void
     */
    private function handleError(ExportQueueInterface $exportQueue, string $errorMessage): void
    {
        $this->logger->error(
            __(
                'An error occurred while sending the data to the Bloomreach. Export Queue ID: %1. ' .
                'Entity type: %2.  Error: %3',
                $exportQueue->getEntityId(),
                $exportQueue->getEntityType(),
                $errorMessage,
            )
        );
        $exportQueue->setStatus(ExportQueueModel::STATUS_ERROR);
        $failedAttempts = $exportQueue->getFailedSendingAttempts() + 1;
        $exportQueue->setTimeOfNextSendingAttempt($this->calculateTimeOfNextSendingAttempt->execute($failedAttempts));
        $exportQueue->setFailedSendingAttempts($failedAttempts);
        $this->debugger->log(
            __(
                'Export data for Export Queue with ID: %1 completed with error. '
                . 'For more information, see in the log file %2',
                $exportQueue->getEntityId(),
                '<project_dir>/var/log/bloomreach/engagement_connector.log'
            )
        );
    }
}
