<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Plugin\Checkout\CustomerData;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventsCollector;
use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventSettings;
use Magento\Checkout\CustomerData\Cart as Subject;

/**
 * The class is responsible for adding cart update events
 */
class AddCartUpdateEvent
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CartUpdateEventsCollector
     */
    private $cartUpdateEventsCollector;

    /**
     * @var CartUpdateEventSettings
     */
    private $cartUpdateEventSettings;

    /**
     * @param ConfigProvider $configProvider
     * @param CartUpdateEventsCollector $cartUpdateEventsCollector
     * @param CartUpdateEventSettings $cartUpdateEventSettings
     */
    public function __construct(
        ConfigProvider $configProvider,
        CartUpdateEventsCollector $cartUpdateEventsCollector,
        CartUpdateEventSettings $cartUpdateEventSettings
    ) {
        $this->configProvider = $configProvider;
        $this->cartUpdateEventsCollector = $cartUpdateEventsCollector;
        $this->cartUpdateEventSettings = $cartUpdateEventSettings;
    }

    /**
     * Adds cart update events
     *
     * @param Subject $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSectionData(Subject $subject, array $result)
    {
        if (!$this->configProvider->isEnabled()
            || !$this->configProvider->isJsSdkEnabled()
            || !$this->configProvider->isCartUpdateTrackingEnabled()
        ) {
            return $result;
        }

        if ($this->cartUpdateEventSettings->isCartUpdated()) {
            $result['bloomreachEvents'] = [
                'version' => time() . '-' . uniqid(),
                'eventsList' => $this->cartUpdateEventsCollector->getEvents()
            ];

            $this->cartUpdateEventSettings->clearCartUpdatedData();
        }

        return $result;
    }
}
