<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Block\Adminhtml\InitialImport;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\Export\ExportProcessor;
use Bloomreach\EngagementConnector\Model\Export\File\MediaUrlGenerator;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\Export\Queue\Source\StatusSource as ExportQueueStatusSource;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource as InitialExportStatusSource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection as ExportQueueCollection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory as ExportQueueCollectionFactory;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use InvalidArgumentException;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The Block is responsible for rendering current import progress
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CurrentProgress extends Template
{
    private const TEMPLATE = 'Bloomreach_EngagementConnector::initial_import/current-progress.phtml';

    /**
     * @var string
     */
    private $entityType;

    /**
     * @var MediaUrlGenerator
     */
    private $mediaUrlGenerator;

    /**
     * @var ExportQueueCollectionFactory
     */
    private $exportQueueCollectionFactory;

    /**
     * @var InitialExportStatusGetter
     */
    private $initialExportStatusGetter;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ExportQueueStatusSource
     */
    private $exportQueueStatusSource;

    /**
     * @var InitialExportStatusInterface
     */
    private $initialExportStatus;

    /**
     * @var ExportQueueCollection
     */
    private $exportQueueCollection;

    /**
     * @param Context $context
     * @param MediaUrlGenerator $mediaUrlGenerator
     * @param ExportQueueCollectionFactory $exportQueueCollectionFactory
     * @param InitialExportStatusGetter $initialExportStatusGetter
     * @param SerializerInterface $jsonSerializer
     * @param ExportQueueStatusSource $exportQueueStatusSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        MediaUrlGenerator $mediaUrlGenerator,
        ExportQueueCollectionFactory $exportQueueCollectionFactory,
        InitialExportStatusGetter $initialExportStatusGetter,
        SerializerInterface $jsonSerializer,
        ExportQueueStatusSource $exportQueueStatusSource,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->mediaUrlGenerator = $mediaUrlGenerator;
        $this->exportQueueCollectionFactory = $exportQueueCollectionFactory;
        $this->initialExportStatusGetter = $initialExportStatusGetter;
        $this->jsonSerializer = $jsonSerializer;
        $this->exportQueueStatusSource = $exportQueueStatusSource;
    }

    /**
     * Get Entity type
     *
     * @return string
     * @throws LocalizedException
     */
    public function getEntityType(): string
    {
        if (!$this->entityType) {
            throw new LocalizedException(__('The entity type is not set'));
        }

        return $this->entityType;
    }

    /**
     * Set Entity type
     *
     * @param string $entityType
     *
     * @return $this
     */
    public function setEntityType(string $entityType): CurrentProgress
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get export file url
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return string
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function getFileUrl(ExportQueueInterface $exportQueue): string
    {
        return $this->mediaUrlGenerator->execute($exportQueue->getEntityType(), $exportQueue->getBody());
    }

    /**
     * Get Export Queue collection
     *
     * @return ExportQueueCollection
     * @throws LocalizedException
     */
    public function getExportQueueCollection(): ExportQueueCollection
    {
        if ($this->exportQueueCollection === null) {
            /** @var ExportQueueCollection $collection */
            $this->exportQueueCollection = $this->exportQueueCollectionFactory->create();
            $this->exportQueueCollection->addFieldToFilter(
                ExportQueueModel::API_TYPE,
                AddInitialExportDataToExportQueue::API_TYPE
            );
            $this->exportQueueCollection->addFieldToFilter(
                ExportQueueModel::ENTITY_TYPE,
                $this->getEntityType()
            );
        }

        return $this->exportQueueCollection;
    }

    /**
     * Checks whether if initial export has been started
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isStarted(): bool
    {
        return $this->getInitialExportStatus()->getStatus() === InitialExportStatusSource::PROCESSING;
    }

    /**
     * Get total queued items
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTotalQueuedItems(): int
    {
        $totalProcessed = $this->getInitialExportStatus()->getTotalErrorItems();

        /** @var ExportQueueInterface $item */
        foreach ($this->getExportQueueCollection() as $item) {
            $totalProcessed += $item->getNumberOfItems();
        }

        return (int) $totalProcessed;
    }

    /**
     * Get total processed items
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTotalProcessedItems(): int
    {
        $totalCompleted = $this->getInitialExportStatus()->getTotalErrorItems();

        /** @var ExportQueueInterface $item */
        foreach ($this->getExportQueueCollection() as $item) {
            if ($item->getStatus() === ExportQueueModel::STATUS_COMPLETE
                || ($item->getStatus() === ExportQueueModel::STATUS_ERROR
                    && $item->getRetries() >= ExportProcessor::MAX_RETRIES
                )
            ) {
                $totalCompleted += $item->getNumberOfItems();
            }
        }

        return (int) $totalCompleted;
    }

    /**
     * Return total items to export
     *
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getTotalItems(): int
    {
        return (int) $this->getInitialExportStatus()->getTotalItems();
    }

    /**
     * Format time of next sending attempt
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return string
     */
    public function getTimeOfNextSendingAttempt(ExportQueueInterface $exportQueue): string
    {
        return $exportQueue->getStatus() !== ExportQueueModel::STATUS_COMPLETE
        && $exportQueue->getTimeOfNextSendingAttempt() > 0
        && $exportQueue->getRetries() < ExportProcessor::MAX_RETRIES
            ? $this->formatDate(
                date('Y-m-d H:i:s', $exportQueue->getTimeOfNextSendingAttempt()),
                \IntlDateFormatter::MEDIUM,
                true
            ) : '';
    }

    /**
     * Display entities range
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return string
     */
    public function getEntitiesRange(ExportQueueInterface $exportQueue): string
    {
        $additionalData = $this->extractAdditionalData($exportQueue);

        if (!$additionalData || !is_array($additionalData)) {
            return '';
        }

        $firstIdInFile = $additionalData[AddInitialExportDataToExportQueue::FIRST_ID_IN_FILE] ?? '';
        $lastIdInFile = $additionalData[AddInitialExportDataToExportQueue::LAST_ID_IN_FILE] ?? '';

        return $firstIdInFile && $lastIdInFile ? sprintf('%s-%s', $firstIdInFile, $lastIdInFile) : '';
    }

    /**
     * Decorates export queue status
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return string
     */
    public function getDecoratedStatus(ExportQueueInterface $exportQueue): string
    {
        switch ($exportQueue->getStatus()) {
            case ExportQueueModel::STATUS_COMPLETE:
                $rowClass = 'notice';
                break;
            case ExportQueueModel::STATUS_ERROR:
                $rowClass = 'major';
                break;
            default:
                $rowClass = 'minor';
                break;
        }

        return sprintf(
            '<span class="grid-severity-%s"><span>%s</span></span>',
            $rowClass,
            $this->exportQueueStatusSource->getStatusLabel($exportQueue->getStatus())
        );
    }

    /**
     * Set template
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * Get InitialExportStatus entity
     *
     * @return InitialExportStatusInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getInitialExportStatus(): InitialExportStatusInterface
    {
        if ($this->initialExportStatus === null) {
            $this->initialExportStatus = $this->initialExportStatusGetter->execute($this->getEntityType());
        }

        return $this->initialExportStatus;
    }

    /**
     * Extract additional data
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return array|bool|float|int|string|null
     */
    private function extractAdditionalData(ExportQueueInterface $exportQueue)
    {
        try {
            return $this->jsonSerializer->unserialize($exportQueue->getAdditionalData());
        } catch (InvalidArgumentException $e) {
            return [];
        }
    }
}
