<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Condition;

use Bloomreach\EngagementConnector\System\ConfigPathGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for checking if feed is enabled for entity type
 */
class IsEntityTypeFeedEnabled
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
     * @var array
     */
    private $cache = [];

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
     * Checks whether if feed is enabled for an entity type
     *
     * @param string $entityType
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     * @throws LocalizedException
     */
    public function execute(
        string $entityType,
        string $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ): bool {
        if (!array_key_exists($entityType, $this->cache)) {
            $this->cache[$entityType] = $this->isAllowed($entityType, $scopeType, $scopeCode);
        }

        return $this->cache[$entityType];
    }

    /**
     * Is Allowed for an entity type
     *
     * @param string $entityType
     * @param string $scopeType
     * @param string|null $scopeCode
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isAllowed(
        string $entityType,
        string $scopeType,
        $scopeCode
    ): bool {
        return $this->scopeConfig->isSetFlag(
            $this->configPathGetter->get($entityType, ConfigProvider::FEED_ENABLED_TYPE),
            $scopeType,
            $scopeCode
        );
    }
}
