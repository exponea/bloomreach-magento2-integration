<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus\ExportQueue;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\GetInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\SaveInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\Export\ExportProcessor;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\AddNewErrorMessage;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * The class is responsible for updating initial export progress
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateProgress
{
    /**
     * @var GetInitialExportStatusInterface
     */
    private $getInitialExportStatus;

    /**
     * @var SaveInitialExportStatusInterface
     */
    private $saveInitialExportStatus;

    /**
     * @var AddNewErrorMessage
     */
    private $addNewErrorMessage;

    /**
     * @param GetInitialExportStatusInterface $getInitialExportStatus
     * @param SaveInitialExportStatusInterface $saveInitialExportStatus
     * @param AddNewErrorMessage $addNewErrorMessage
     */
    public function __construct(
        GetInitialExportStatusInterface $getInitialExportStatus,
        SaveInitialExportStatusInterface $saveInitialExportStatus,
        AddNewErrorMessage $addNewErrorMessage
    ) {
        $this->getInitialExportStatus = $getInitialExportStatus;
        $this->saveInitialExportStatus = $saveInitialExportStatus;
        $this->addNewErrorMessage = $addNewErrorMessage;
    }

    /**
     * Updates Initial Export progress
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return void
     */
    public function execute(ExportQueueInterface $exportQueue): void
    {
        if ($exportQueue->getApiType() !== AddInitialExportDataToExportQueue::API_TYPE) {
            return;
        }

        try {
            $this->update($exportQueue);
        } catch (CouldNotSaveException | NoSuchEntityException $e) {
            return;
        }
    }

    /**
     * Updates Initial Export progress
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    private function update(ExportQueueInterface $exportQueue): void
    {
        $initialExportStatus = $this->getInitialExportStatus->execute($exportQueue->getEntityType());

        if ($exportQueue->getStatus() === ExportQueueModel::STATUS_COMPLETE) {
            $initialExportStatus->setTotalExported(
                $initialExportStatus->getTotalExported() + $exportQueue->getNumberOfItems()
            );
        } elseif ($exportQueue->getStatus() === ExportQueueModel::STATUS_ERROR
            && $exportQueue->getRetries() === ExportProcessor::MAX_RETRIES
        ) {
            $initialExportStatus->setTotalErrorItems(
                $initialExportStatus->getTotalErrorItems() + $exportQueue->getNumberOfItems()
            );
        }

        $tempTotal = $initialExportStatus->getTotalExported() + $initialExportStatus->getTotalErrorItems();
        if ($initialExportStatus->getTotalItems() <= $tempTotal) {
            if ($initialExportStatus->getTotalErrorItems() === 0) {
                $initialExportStatus->setStatus(StatusSource::SUCCESS);
            } else {
                $initialExportStatus->setStatus(StatusSource::ERROR);
            }
            $initialExportStatus->setFinishedAt(date('Y-m-d H:i:s'));
        } else {
            $initialExportStatus->setStatus(StatusSource::PROCESSING);
        }

        if ($exportQueue->getErrorMessage()) {
            $this->addNewErrorMessage->execute($initialExportStatus, $exportQueue->getErrorMessage());
        }

        $this->saveInitialExportStatus->execute($initialExportStatus);
    }
}
