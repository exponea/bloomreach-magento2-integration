<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for returning catalog id for entity type
 */
class CatalogIdResolver
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ConfigPathGetter
     */
    private $configPathGetter;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigPathGetter $configPathGetter
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigPathGetter $configPathGetter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configPathGetter = $configPathGetter;
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
    public function getCatalogId(
        string $entityType,
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeCode = 0
    ): string {
        return (string) $this->scopeConfig->getValue(
            $this->configPathGetter->get($entityType, ConfigProvider::CATALOG_ID_TYPE),
            $scopeType,
            $scopeCode
        );
    }
}
