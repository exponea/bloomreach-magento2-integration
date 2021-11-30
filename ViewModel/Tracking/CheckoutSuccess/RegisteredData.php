<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\ViewModel\Tracking\CheckoutSuccess;

use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * This class is responsible for generating registered data on the checkout success page
 */
class RegisteredData implements ArgumentInterface
{
    /**
     * @var RegisteredGenerator
     */
    private $registeredGenerator;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param Session $checkoutSession
     * @param RegisteredGenerator $registeredGenerator
     */
    public function __construct(
        Session $checkoutSession,
        RegisteredGenerator $registeredGenerator
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->registeredGenerator = $registeredGenerator;
    }

    /**
     * Returns registered data
     *
     * @return string
     */
    public function getRegisteredData(): string
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $customerId = $order->getCustomerId() ? (int) $order->getCustomerId() : null;

        return $this->registeredGenerator->generateSerialized($order->getCustomerEmail(), $customerId);
    }
}
