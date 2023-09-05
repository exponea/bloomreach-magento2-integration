<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Sender;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Bloomreach\EngagementConnector\Exception\ExportRequestException;
use Bloomreach\EngagementConnector\Logger\Debugger;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\Export\Queue\Sender\Handler\ErrorHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Registry\RunQueue as RunQueueRegistry;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ExportQueue\UpdateProgress;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for processing export queue item sending to the Bloomreach service
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var Debugger
     */
    private $debugger;

    /**
     * @var UpdateProgress
     */
    private $updateInitialExportStatusProgress;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RunQueueRegistry
     */
    private $runQueueRegistry;

    /**
     * @var array
     */
    private $lastExportSendTime = [];

    /**
     * @param SaveExportQueueInterface $saveExportQueue
     * @param TransporterInterface $transporterResolver
     * @param ErrorHandler $errorHandler
     * @param Debugger $debugger
     * @param UpdateProgress $updateInitialExportStatusProgress
     * @param LoggerInterface $logger
     * @param RunQueueRegistry $runQueueRegistry
     */
    public function __construct(
        SaveExportQueueInterface $saveExportQueue,
        TransporterInterface $transporterResolver,
        ErrorHandler $errorHandler,
        Debugger $debugger,
        UpdateProgress $updateInitialExportStatusProgress,
        LoggerInterface $logger,
        RunQueueRegistry $runQueueRegistry
    ) {
        $this->saveExportQueue = $saveExportQueue;
        $this->transporterResolver = $transporterResolver;
        $this->errorHandler = $errorHandler;
        $this->debugger = $debugger;
        $this->updateInitialExportStatusProgress = $updateInitialExportStatusProgress;
        $this->logger = $logger;
        $this->runQueueRegistry = $runQueueRegistry;
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
        /**
         * Only one "Run import" request per minute is allowed, otherwise api will return an 429 HTTP error code
         */
        if (!$this->canSend($exportQueue)) {
            return;
        }

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
        $this->updateInitialExportStatusProgress->execute($exportQueue);
        $this->setLastSendTime($exportQueue);
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
            if ($exportQueue->getStatus() === ExportQueueModel::STATUS_COMPLETE) {
                $this->runQueueRegistry->addToNewItemsCount($exportQueue->getNumberOfItems());
            }
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
        $this->errorHandler->handle($exportQueue, $errorMessage);
        $this->debugger->log(
            __(
                'Export data for Export Queue with ID: %1 completed with error. '
                . 'For more information, see in the log file %2',
                $exportQueue->getEntityId(),
                '<project_dir>/var/log/bloomreach/engagement_connector.log'
            )
        );
    }

    /**
     * Checks whether a queue item can be sent
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     */
    private function canSend(ExportQueueInterface $exportQueue): bool
    {
        $lastSendTime = $this->lastExportSendTime[$exportQueue->getEntityType()] ?? null;

        if (!$lastSendTime || $exportQueue->getApiType() !== AddInitialExportDataToExportQueue::API_TYPE) {
            return true;
        }

        return $lastSendTime > time() + 60;
    }

    /**
     * Sets the time the queue item was last sent
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return void
     */
    private function setLastSendTime(ExportQueueInterface $exportQueue): void
    {
        if ($exportQueue->getApiType() !== AddInitialExportDataToExportQueue::API_TYPE) {
            return;
        }

        $this->lastExportSendTime[$exportQueue->getEntityType()] = time();
    }
}
