<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Queue\Sender;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Bloomreach\EngagementConnector\Exception\ExportRequestException;
use Bloomreach\EngagementConnector\Logger\Debugger;
use Bloomreach\EngagementConnector\Model\Export\Queue\Sender\Handler\ErrorHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\Batch\Transporter;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Registry\RunQueue as RunQueueRegistry;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for sending batch of items
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SenderBatchProcessor
{
    /**
     * @var Transporter
     */
    private $transporter;

    /**
     * @var SaveExportQueueInterface
     */
    private $saveExportQueue;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var Debugger
     */
    private $debugger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RunQueueRegistry
     */
    private $runQueueRegistry;

    /**
     * @param Transporter $transporter
     * @param SaveExportQueueInterface $saveExportQueue
     * @param ErrorHandler $errorHandler
     * @param SerializerInterface $jsonSerializer
     * @param Debugger $debugger
     * @param LoggerInterface $logger
     * @param RunQueueRegistry $runQueueRegistry
     */
    public function __construct(
        Transporter $transporter,
        SaveExportQueueInterface $saveExportQueue,
        ErrorHandler $errorHandler,
        SerializerInterface $jsonSerializer,
        Debugger $debugger,
        LoggerInterface $logger,
        RunQueueRegistry $runQueueRegistry
    ) {
        $this->transporter = $transporter;
        $this->saveExportQueue = $saveExportQueue;
        $this->errorHandler = $errorHandler;
        $this->jsonSerializer = $jsonSerializer;
        $this->debugger = $debugger;
        $this->logger = $logger;
        $this->runQueueRegistry = $runQueueRegistry;
    }

    /**
     * Sends list of items
     *
     * @param ExportQueueInterface[] $exportQueueList
     *
     * @return void
     */
    public function process(array $exportQueueList): void
    {
        try {
            $exportQueueList = $this->setItemInProgress($exportQueueList);
            $this->debugger->log(
                __(
                    'Export batch of Export Queue Items with IDs: %entity_ids started.',
                    ['entity_ids' => implode(' ,', array_keys($exportQueueList))]
                )
            );
            $isSuccessful = $this->handleResponse($this->transporter->send($exportQueueList), $exportQueueList);
            $this->saveItems($exportQueueList);

            if ($isSuccessful) {
                $this->debugger->log(
                    __(
                        'Export batch of Export Queue Items with IDs: %entity_ids completed successfully.',
                        ['entity_ids' => implode(' ,', array_keys($exportQueueList))]
                    )
                );
            } else {
                $this->debugger->log(
                    __(
                        'Export batch of Export Queue Items with IDs: %entity_ids completed with errors.',
                        ['entity_ids' => implode(' ,', array_keys($exportQueueList))]
                    )
                );
            }

        } catch (ExportRequestException $e) {
            $this->handleException($exportQueueList, $e);
            $this->saveItems($exportQueueList, $e->isNeedUpdateRetryCounter());
        } catch (Exception $e) {
            $this->handleException($exportQueueList, $e);
            $this->saveItems($exportQueueList);
        }
    }

    /**
     * Handle exception
     *
     * @param array $exportQueueList
     * @param Exception $exception
     *
     * @return void
     */
    private function handleException(array $exportQueueList, Exception $exception): void
    {
        $this->logger->error(
            __(
                'An error occurred while sending the data to the Bloomreach. Export Queue IDs: %entity_ids. '
                . 'Original error message: %error_message',
                [
                    'entity_ids' => implode(' ,', array_keys($exportQueueList)),
                    'error_message' => $exception->getMessage()
                ]
            )
        );

        $this->handleError($exportQueueList, $exception->getMessage());
        $this->debugger->log(
            __(
                'Export batch of Export Queue Items with IDs: %entity_ids completed with error. '
                . 'For more information, see in the log file %log_file',
                [
                    'entity_ids' => implode(' ,', array_keys($exportQueueList)),
                    'log_file' => '<project_dir>/var/log/bloomreach/engagement_connector.log'
                ]
            )
        );
    }

    /**
     * Handle sending error for list of items
     *
     * @param array $exportQueueList
     * @param string $errorMessage
     *
     * @return void
     */
    private function handleError(array $exportQueueList, string $errorMessage): void
    {
        foreach ($exportQueueList as $exportQueue) {
            $this->handleItemError($exportQueue, $errorMessage);
        }
    }

    /**
     * Handle sending error for one item
     *
     * @param ExportQueueInterface $exportQueue
     * @param string $errorMessage
     *
     * @return void
     */
    private function handleItemError(ExportQueueInterface $exportQueue, string $errorMessage): void
    {
        $this->debugger->log(
            __(
                'Export Queue ID: %entity_id',
                ['entity_id' => $exportQueue->getEntityId()]
            )
        );
        $this->debugger->log(
            __(
                'Entity Type: %entity_type',
                ['entity_type' => $exportQueue->getEntityType()]
            )
        );
        $this->debugger->log(
            __(
                'Api Type: %api_type',
                ['api_type' => $exportQueue->getApiType()]
            )
        );
        $this->errorHandler->handle($exportQueue, $errorMessage);
        $this->debugger->log(
            __(
                'Original error message: %error_message',
                ['error_message' => $errorMessage]
            )
        );
    }

    /**
     * Handle response
     *
     * @param ResponseInterface $response
     * @param ExportQueueInterface[] $exportQueueList
     *
     * @return bool
     * @throws LocalizedException
     */
    private function handleResponse(ResponseInterface $response, array $exportQueueList): bool
    {
        $result = $response->getData()['results'] ?? [];

        if (!$result) {
            throw new LocalizedException(__('Response is not valid. Result object is empty'));
        }

        $handledItems = [];
        $isSuccessful = true;

        foreach ($result as $item) {
            $commandId = $item['command_id'] ?? null;

            if (!$commandId) {
                continue;
            }

            $exportQueueItem = $exportQueueList[$commandId] ?? null;

            if (!$exportQueueItem) {
                continue;
            }

            if ($this->handleItem($item, $exportQueueItem)) {
                $handledItems[$exportQueueItem->getEntityId()] = $exportQueueItem;
                $isSuccessful = $exportQueueItem->getStatus() !== ExportQueueModel::STATUS_ERROR;
            }
        }

        $notHandledItems = array_diff_key($exportQueueList, $handledItems);

        if ($notHandledItems) {
            $this->handleError($notHandledItems, __('Response is not valid')->render());
            $isSuccessful = false;
        }

        return $isSuccessful;
    }

    /**
     * Handles item
     *
     * @param array $item
     * @param ExportQueueInterface $exportQueueItem
     *
     * @return bool
     */
    private function handleItem(array $item, ExportQueueInterface $exportQueueItem): bool
    {
        $isSuccess = $item['success'] ?? null;

        if (!$isSuccess === null) {
            return false;
        }

        if ($isSuccess) {
            $exportQueueItem->setStatus(ExportQueueModel::STATUS_COMPLETE);
            return true;
        }

        $errors = $item['errors'] ?? [];

        if ($errors) {
            $errorMessage = $this->jsonSerializer->serialize($errors);
            $this->handleItemError($exportQueueItem, $errorMessage);

            return true;
        }

        return false;
    }

    /**
     * Save Export queue items list
     *
     * @param array $exportQueueList
     * @param bool $updateRetries
     *
     * @return void
     */
    private function saveItems(array $exportQueueList, bool $updateRetries = true): void
    {
        foreach ($exportQueueList as $exportQueue) {
            $this->saveExportQueue($exportQueue, $updateRetries);
        }
    }

    /**
     * Save export queue
     *
     * @param ExportQueueInterface $exportQueue
     * @param bool $updateRetries
     *
     * @return void
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    private function saveExportQueue(ExportQueueInterface $exportQueue, bool $updateRetries): void
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
                    'An error occurred while saving the Export Queue ID: %entity_id. ' .
                    'Entity type: %entity_type. Original error message: %error_message',
                    [
                        'entity_id' => $exportQueue->getEntityId(),
                        'entity_type' => $exportQueue->getEntityType(),
                        'error_message' => $e->getMessage()
                    ]
                )
            );
        }
    }

    /**
     * Set in progress status for items
     *
     * @param ExportQueueInterface[] $exportQueueList
     *
     * @return array
     * @throws CouldNotSaveException
     */
    private function setItemInProgress(array $exportQueueList): array
    {
        $list = [];

        foreach ($exportQueueList as $exportQueue) {
            $exportQueue->setStatus(ExportQueueModel::STATUS_IN_PROGRESS);
            $list[$exportQueue->getEntityId()] = $this->saveExportQueue->execute($exportQueue);
        }

        return $list;
    }
}
