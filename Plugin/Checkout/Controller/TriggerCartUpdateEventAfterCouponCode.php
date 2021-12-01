<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Plugin\Checkout\Controller;

use Bloomreach\EngagementConnector\Model\Tracking\Event\Cart\CartUpdateEventSettings;
use Magento\Checkout\Controller\Cart\CouponPost as Subject;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * The class is responsible for triggering cart update event sending
 */
class TriggerCartUpdateEventAfterCouponCode
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartUpdateEventSettings
     */
    private $cartUpdateEventSettings;

    /**
     * @var string
     */
    private $oldCouponCode;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CartUpdateEventSettings $cartUpdateEventSettings
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CartUpdateEventSettings $cartUpdateEventSettings
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartUpdateEventSettings = $cartUpdateEventSettings;
    }

    /**
     * Trigger cart update event sending after change coupon code
     *
     * @param Subject $subject
     * @param Redirect $result
     *
     * @return Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(Subject $subject, $result)
    {
        $newCouponCode = trim((string) $subject->getRequest()->getParam('coupon_code'));
        $isRemove = (int) $subject->getRequest()->getParam('remove') === 1;

        if (!strlen($newCouponCode) && !$isRemove) {
            return $result;
        }

        $quote = $this->getQuote();

        if (!$quote) {
            return $result;
        }

        if ((string) $quote->getCouponCode() !== $this->getOldCouponCode()) {
            $this->cartUpdateEventSettings->setIsCartUpdated(CartUpdateEventSettings::UPDATE_ACTION);
        }

        return $result;
    }

    /**
     * Save old coupon code
     *
     * @param Subject $subject
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(Subject $subject)
    {
        $quote = $this->getQuote();

        if ($quote) {
            $this->setOldCouponCode((string) $quote->getCouponCode());
        }
    }

    /**
     * Returns current quote
     *
     * @return CartInterface|null
     */
    private function getQuote(): ?CartInterface
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException | LocalizedException $e) {
            return null;
        }
    }

    /**
     * Set old coupon code
     *
     * @param string|null $couponCode
     *
     * @return void
     */
    private function setOldCouponCode(string $couponCode): void
    {
        $this->oldCouponCode = $couponCode;
    }

    /**
     * Get olc coupon code
     *
     * @return string
     */
    private function getOldCouponCode(): string
    {
        return (string) $this->oldCouponCode;
    }
}
