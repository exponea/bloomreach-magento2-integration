<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Plugin\Quote;

use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventSettings;
use Magento\Quote\Model\Quote as Subject;

/**
 * The class is responsible for triggering cart update event sending
 */
class TriggerCartUpdateEventAfterCartTruncate
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
     * Triggers cart update event sending after remove all items
     *
     * @param Subject $subject
     * @param Subject $result
     *
     * @return Subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRemoveAllItems(Subject $subject, $result)
    {
        $this->cartUpdateEventSettings->setIsCartUpdated(CartUpdateEventSettings::EMPTY_ACTION);

        return $result;
    }

    /**
     * Triggers cart update event sending after merge coupon codes
     *
     * @param Subject $subject
     * @param Subject $quote
     *
     * @return array
     */
    public function beforeMerge(Subject $subject, Subject $quote): array
    {
        if ((string) $subject->getCouponCode() !== (string) $quote->getCouponCode()) {
            $this->cartUpdateEventSettings->setIsCartUpdated(CartUpdateEventSettings::UPDATE_ACTION);
        }

        return [$quote];
    }
}
