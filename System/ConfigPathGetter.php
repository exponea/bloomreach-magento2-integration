<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\System;

use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for getting config path by entity type
 */
class ConfigPathGetter
{
    /**
     * @var array
     */
    private $configPool;

    /**
     * @param array $configPool
     */
    public function __construct(array $configPool = [])
    {
        $this->configPool = $configPool;
    }

    /**
     * Returns config path by entityType
     *
     * @param string $entityType
     * @param string $configType
     *
     * @return string
     * @throws LocalizedException
     */
    public function get(string $entityType, string $configType): string
    {
        $configPath = $this->configPool[$entityType][$configType] ?? '';

        if (!$configPath) {
            throw new LocalizedException(
                __(
                    'There is no %config_type config for %entity_type',
                    [
                        'entity_type' => $entityType,
                        'config_type' => $configType
                    ]
                )
            );
        }

        return $configPath;
    }
}
