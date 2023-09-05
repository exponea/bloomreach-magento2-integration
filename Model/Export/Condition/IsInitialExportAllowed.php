<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Condition;

use Bloomreach\EngagementConnector\System\ImportIdResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for checking if initial export allowed for entity type
 */
class IsInitialExportAllowed
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
     * Checks if initial export allowed
     *
     * @param string $entityType
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function execute(
        string $entityType,
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        try {
            return $this->importIdResolver->getImportId($entityType, $scopeType, $scopeCode)
                && $this->isEntityTypeFeedEnabled->execute($entityType, $scopeType, $scopeCode);
        } catch (LocalizedException $e) {
            return false;
        }
    }
}
