<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\ViewModel\Tracking;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
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
    public function isEnabled(): bool
    {
        return $this->configProvider->isEnabled()
            && $this->configProvider->isJsSdkEnabled()
            && $this->getProjectId()
            && $this->getApiTarget();
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
