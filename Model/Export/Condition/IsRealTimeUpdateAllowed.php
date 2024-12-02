<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Condition;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * The Class is responsible for checking if realtime updates are allowed for an entity type
 */
class IsRealTimeUpdateAllowed
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $configPool;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var bool
     */
    private $useCache;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param array $configPool
     * @param bool $useCache
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $configPool = [],
        bool $useCache = true
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configPool = $configPool;
        $this->useCache = $useCache;
    }

    /**
     * Checks whether if realtime updates is allowed for an entity type
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
        if (!array_key_exists($entityType, $this->cache) || !$this->useCache) {
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
     */
    private function isAllowed(
        string $entityType,
        string $scopeType,
        $scopeCode
    ): bool {
        $configPool = $this->configPool[$entityType] ?? null;

        if (!$configPool) {
            return true;
        }

        foreach ($configPool as $configPath) {
            if (!$this->scopeConfig->isSetFlag($configPath, $scopeType, $scopeCode)) {
                return false;
            }
        }

        return true;
    }
}
