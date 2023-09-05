<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\ViewModel\Tracking;

use Bloomreach\EngagementConnector\Service\Tracking\IsFrontendTrackingEnabled;
use Bloomreach\EngagementConnector\Service\Tracking\TrackingProviderGetter;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * The class contains methods for obtaining configuration data for frontend tracking
 */
class Config implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var TrackingProviderGetter
     */
    private $getTrackingProvider;

    /**
     * @var IsFrontendTrackingEnabled
     */
    private $isFrontendTrackingEnabled;

    /**
     * @param ConfigProvider $configProvider
     * @param TrackingProviderGetter $getTrackingProvider
     * @param IsFrontendTrackingEnabled $isFrontendTrackingEnabled
     */
    public function __construct(
        ConfigProvider $configProvider,
        TrackingProviderGetter $getTrackingProvider,
        IsFrontendTrackingEnabled $isFrontendTrackingEnabled
    ) {
        $this->configProvider = $configProvider;
        $this->getTrackingProvider = $getTrackingProvider;
        $this->isFrontendTrackingEnabled = $isFrontendTrackingEnabled;
    }

    /**
     * Checks whether is frontend tracking enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isFrontendTrackingEnabled->execute();
    }

    /**
     * Get Tracking Provider Name
     *
     * @return string
     */
    public function getTrackingProvider(): string
    {
        return $this->getTrackingProvider->execute();
    }

    /**
     * Returns project id
     *
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->configProvider->getProjectTokenId();
    }

    /**
     * Returns Api Target
     *
     * @return string
     */
    public function getApiTarget(): string
    {
        return $this->configProvider->getApiTarget();
    }
}
