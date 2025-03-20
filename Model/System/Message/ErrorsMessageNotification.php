<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\System\Message;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\App\CacheInterface;
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
    private const HAS_ERRORS_CACHE_VALUE = 'has_errors';
    private const NO_ERRORS_CACHE_VALUE = 'no_errors';
    private const CACHE_LIFETIME = 600;

    /**
     * @var CollectionFactory
     */
    private $exportQueueCollectionFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CacheInterface
     */
    private $cacheManagement;

    /**
     * @param CollectionFactory $exportQueueCollectionFactory
     * @param ConfigProvider $configProvider
     * @param CacheInterface $cacheManagement
     */
    public function __construct(
        CollectionFactory $exportQueueCollectionFactory,
        ConfigProvider $configProvider,
        CacheInterface $cacheManagement
    ) {
        $this->configProvider = $configProvider;
        $this->exportQueueCollectionFactory = $exportQueueCollectionFactory;
        $this->cacheManagement = $cacheManagement;
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
        return $this->configProvider->isEnabled()
            && $this->configProvider->isSystemNotificationEnabled()
            && $this->isNeedToShow();
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
     * - save data to cache
     *
     * @return bool
     */
    private function isNeedToShow(): bool
    {
        $result = $this->cacheManagement->load($this->getIdentity());

        if (is_string($result) && $result) {
            return $result === self::HAS_ERRORS_CACHE_VALUE;
        }

        $hasErrors = $this->hasRecentErrorRecords();
        $this->cacheManagement->save(
            $hasErrors ? self::HAS_ERRORS_CACHE_VALUE : self::NO_ERRORS_CACHE_VALUE,
            $this->getIdentity(),
            ['BLOCK_HTML'],
            self::CACHE_LIFETIME
        );

        return $hasErrors;
    }

    /**
     * Has recent error records
     *
     * @return bool
     */
    private function hasRecentErrorRecords(): bool
    {
        $periodOfTime = date('Y-m-d H:i:s', strtotime('-24 hours'));
        return (bool) $this->exportQueueCollectionFactory->create()
            ->addFieldToFilter(ExportQueueModel::STATUS, ExportQueueModel::STATUS_ERROR)
            ->addFieldToFilter(ExportQueueModel::UPDATED_AT, ['gteq' => $periodOfTime])
            ->getSize();
    }
}
