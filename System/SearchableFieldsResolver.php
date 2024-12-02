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
 * The class is responsible for returning searchable fields for entity type
 */
class SearchableFieldsResolver
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
     * Get searchable fields by entity type
     *
     * @param string $entityType
     * @param string $scopeType
     * @param null|int|string $scopeCode
     *
     * @return array
     * @throws LocalizedException
     */
    public function get(
        string $entityType,
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): array {
        $value = (string) $this->scopeConfig->getValue(
            $this->configPathGetter->get($entityType, ConfigProvider::SEARCHABLE_FIELDS),
            $scopeType,
            $scopeCode
        );

        return $value ? explode(',', $value) : [];
    }
}
