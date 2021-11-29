<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Observer\CartUpdate;

use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventSettings;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * The class is responsible for triggering cart update event sending
 */
class TriggerCartUpdateEvent implements ObserverInterface
{
    private const ACTIONS = [
        'sales_quote_product_add_after' => CartUpdateEventSettings::ADD_ACTION,
        'sales_quote_remove_item' => CartUpdateEventSettings::REMOVE_ACTION,
        'checkout_cart_update_items_after' => CartUpdateEventSettings::UPDATE_ACTION
    ];

    /**
     * @var CartUpdateEventSettings
     */
    private $cartUpdateEventSettings;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param CartUpdateEventSettings $cartUpdateEventSettings
     * @param RequestInterface $request
     */
    public function __construct(
        CartUpdateEventSettings $cartUpdateEventSettings,
        RequestInterface $request
    ) {
        $this->cartUpdateEventSettings = $cartUpdateEventSettings;
        $this->request = $request;
    }

    /**
     * Triggers cart update event sending
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $action = self::ACTIONS[(string) $observer->getEvent()->getName()] ?? '';

        if ($this->request->getModuleName() === 'checkout'
            && strtolower((string) $this->request->getActionName()) === 'updateitemoptions'
        ) {
            $action = CartUpdateEventSettings::UPDATE_ACTION;
        }

        $this->cartUpdateEventSettings->setIsCartUpdated($action);
    }
}
