<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for returning import id for entity type
 */
class ImportIdResolver
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $importIdsConfigPath;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param array $importIdsConfigPath
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $importIdsConfigPath = []
    ) {
        $this->importIdsConfigPath = $importIdsConfigPath;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get import id by entity type
     *
     * @param string $entityType
     * @param string $scopeType
     * @param null|int|string $scopeCode
     *
     * @return string
     * @throws LocalizedException
     */
    public function getImportId(
        string $entityType,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): string {
        $configPath = $this->importIdsConfigPath[$entityType] ?? '';

        if (!$configPath) {
            throw new LocalizedException(
                __('There is no such config for %2 entity type')
            );
        }

        return (string) $this->scopeConfig->getValue($configPath, $scopeType, $scopeCode);
    }
}
