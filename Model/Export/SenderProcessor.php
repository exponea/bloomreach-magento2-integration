<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param SaveExportQueueInterface $saveExportQueue
     * @param TransporterInterface $transporterResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        SaveExportQueueInterface $saveExportQueue,
        TransporterInterface $transporterResolver,
        LoggerInterface $logger
    ) {
        $this->saveExportQueue = $saveExportQueue;
        $this->transporterResolver = $transporterResolver;
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
            $exportQueue->setStatus(ExportQueueModel::STATUS_IN_PROGRESS);
            $this->saveExportQueue->execute($exportQueue);
            $this->transporterResolver->send($exportQueue);
            $this->saveExportQueue($exportQueue);
            $exportQueue->setStatus(ExportQueueModel::STATUS_COMPLETE);
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while sending the data to the Bloomreach. Export Queue ID: %1. ' .
                    'Entity type: %2.  Error: %3',
                    $exportQueue->getEntityId(),
                    $exportQueue->getEntityType(),
                    $e->getMessage(),
                )
            );
            $exportQueue->setStatus(ExportQueueModel::STATUS_ERROR);
        }

        $this->saveExportQueue($exportQueue, true);
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
}
