<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Tracking;

use Bloomreach\EngagementConnector\System\ConfigProvider;

/**
 * The class check is enabled TrackingProvider
 */
class IsFrontendTrackingEnabled
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @param ConfigProvider $configProvider
     */
    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Checks whether is frontend tracking enabled
     *
     * @return bool
     */
    public function execute(): bool
    {
        return $this->configProvider->isEnabled()
            && ($this->configProvider->isJsSdkEnabled() || $this->configProvider->isDataLayerEnabled())
            && $this->configProvider->getProjectTokenId()
            && $this->configProvider->getApiTarget();
    }
}
