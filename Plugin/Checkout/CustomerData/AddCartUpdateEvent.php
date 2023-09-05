<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Plugin\Checkout\CustomerData;

use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventsCollector;
use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventSettings;
use Bloomreach\EngagementConnector\Service\Tracking\IsFrontendTrackingEnabled;
use Bloomreach\EngagementConnector\System\ConfigProvider;
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
     * @var IsFrontendTrackingEnabled
     */
    private $isFrontendTrackingEnabled;

    /**
     * @param ConfigProvider $configProvider
     * @param CartUpdateEventsCollector $cartUpdateEventsCollector
     * @param CartUpdateEventSettings $cartUpdateEventSettings
     * @param IsFrontendTrackingEnabled $isFrontendTrackingEnabled
     */
    public function __construct(
        ConfigProvider $configProvider,
        CartUpdateEventsCollector $cartUpdateEventsCollector,
        CartUpdateEventSettings $cartUpdateEventSettings,
        IsFrontendTrackingEnabled $isFrontendTrackingEnabled
    ) {
        $this->configProvider = $configProvider;
        $this->cartUpdateEventsCollector = $cartUpdateEventsCollector;
        $this->cartUpdateEventSettings = $cartUpdateEventSettings;
        $this->isFrontendTrackingEnabled = $isFrontendTrackingEnabled;
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
        if (!$this->isFrontendTrackingEnabled->execute()
            && !$this->configProvider->isCartUpdateTrackingEnabled()
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
