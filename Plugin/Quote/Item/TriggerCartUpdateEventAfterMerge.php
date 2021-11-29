<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Plugin\Quote\Item;

use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventSettings;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Processor as Subject;

/**
 * The class is responsible for triggering cart update event sending
 */
class TriggerCartUpdateEventAfterMerge
{
    /**
     * @var CartUpdateEventSettings
     */
    private $cartUpdateEventSettings;

    /**
     * @param CartUpdateEventSettings $cartUpdateEventSettings
     */
    public function __construct(CartUpdateEventSettings $cartUpdateEventSettings)
    {
        $this->cartUpdateEventSettings = $cartUpdateEventSettings;
    }

    /**
     * Triggers cart update event sending
     *
     * @param Subject $subject
     * @param CartItemInterface $result
     * @param Item $source
     * @param Item $target
     *
     * @return CartItemInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMerge(
        Subject $subject,
        CartItemInterface $result,
        Item $source,
        Item $target
    ): CartItemInterface {
        $this->cartUpdateEventSettings->setIsCartUpdated(CartUpdateEventSettings::UPDATE_ACTION);

        return $result;
    }
}
