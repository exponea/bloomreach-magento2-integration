<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Tracking;

use Bloomreach\EngagementConnector\System\ConfigProvider;

/**
 * The class get TrackingProvider
 */
class TrackingProviderGetter
{
    private const ALL = 'all';

    private const DATALAYER = 'dataLayer';

    private const EXPONEA = 'exponea';

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
     * Get Tracking Provider Name
     *
     * @return string
     */
    public function execute(): string
    {
        switch (true) {
            case $this->configProvider->isJsSdkEnabled() && $this->configProvider->isDataLayerEnabled():
                $trackingName = self::ALL;
                break;
            case $this->configProvider->isDataLayerEnabled():
                $trackingName = self::DATALAYER;
                break;
            case $this->configProvider->isJsSdkEnabled():
                $trackingName = self::EXPONEA;
                break;
            default:
                $trackingName = '';
                break;
        }

        return $trackingName;
    }
}
