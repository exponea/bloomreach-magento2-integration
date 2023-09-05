<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsEntityTypeFeedEnabled;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\System\ImportIdResolver;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for resolving an initial export progress status
 */
class ProgressStatusResolver
{
    /**
     * @var IsEntityTypeFeedEnabled
     */
    private $isEntityTypeFeedEnabled;

    /**
     * @var ImportIdResolver
     */
    private $importIdResolver;

    /**
     * @param IsEntityTypeFeedEnabled $isEntityTypeFeedEnabled
     * @param ImportIdResolver $importIdResolver
     */
    public function __construct(
        IsEntityTypeFeedEnabled $isEntityTypeFeedEnabled,
        ImportIdResolver $importIdResolver
    ) {
        $this->isEntityTypeFeedEnabled = $isEntityTypeFeedEnabled;
        $this->importIdResolver = $importIdResolver;
    }

    /**
     * Resolves initial export status
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return int
     */
    public function execute(InitialExportStatusInterface $initialExportStatus): int
    {
        if (!$this->isEntityTypeFeedEnabled($initialExportStatus->getEntityType())) {
            return StatusSource::DISABLED;
        } elseif (!$this->hasImportId($initialExportStatus->getEntityType())) {
            return StatusSource::NOT_READY;
        } elseif ($initialExportStatus->getStatus()) {
            return $initialExportStatus->getStatus();
        }

        return StatusSource::READY;
    }

    /**
     * Checks if entity type feed is enabled
     *
     * @param string $entityType
     *
     * @return bool
     */
    private function isEntityTypeFeedEnabled(string $entityType): bool
    {
        try {
            return $this->isEntityTypeFeedEnabled->execute($entityType);
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * Checks if entity type has import ID
     *
     * @param string $entityType
     *
     * @return bool
     */
    private function hasImportId(string $entityType): bool
    {
        try {
            return !!$this->importIdResolver->getImportId($entityType);
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
