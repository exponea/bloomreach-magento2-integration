<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\System\Message;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Phrase;

/**
 * Show notification if error happened last 24 hours
 */
class ErrorsMessageNotification implements MessageInterface
{
    /**
     * Message identity
     */
    private const MESSAGE_IDENTITY = 'bloomreach_errors_system_notification';

    /**
     * @var CollectionFactory
     */
    private $exportQueueCollectionFactory;

    /**
     * @param CollectionFactory $exportQueueCollectionFactory
     */
    public function __construct(
        CollectionFactory $exportQueueCollectionFactory
    ) {
        $this->exportQueueCollectionFactory = $exportQueueCollectionFactory;
    }

    /**
     * Retrieve unique system message identity
     *
     * @return string
     */
    public function getIdentity(): string
    {
        return self::MESSAGE_IDENTITY;
    }

    /**
     * Check whether the system message should be shown
     *
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return $this->isNeedToShow();
    }

    /**
     * Retrieve system message text
     *
     * @return Phrase
     */
    public function getText(): Phrase
    {
        return __('There has been an error in sending data to Bloomreach during the last 24 hours. '
            . 'Details in Bloomreach log files.');
    }

    /**
     * Retrieve system message severity
     * Possible default system message types:
     * - MessageInterface::SEVERITY_CRITICAL
     * - MessageInterface::SEVERITY_MAJOR
     * - MessageInterface::SEVERITY_MINOR
     * - MessageInterface::SEVERITY_NOTICE
     *
     * @return int
     */
    public function getSeverity(): int
    {
        return self::SEVERITY_MAJOR;
    }

    /**
     * Show the error message if need it
     *
     * @return bool
     */
    private function isNeedToShow(): bool
    {
        $periodOfTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
        return (bool) $this->exportQueueCollectionFactory->create()
            ->addFieldToFilter(ExportQueueModel::STATUS, ExportQueueModel::STATUS_ERROR)
            ->addFieldToFilter(ExportQueueModel::UPDATED_AT, ['gteq' => $periodOfTime])
            ->getSize();
    }
}
