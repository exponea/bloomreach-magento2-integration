<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Tracking\Event\Cart;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\CartUpdateEvent;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\Tracking\Event\EventsInterface;
use Bloomreach\EngagementConnector\Model\Tracking\EventBuilderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * The class is responsible for generating cart update event
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CartUpdate implements EventsInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

    /**
     * @var EventBuilderFactory
     */
    private $eventBuilderFactory;

    /**
     * @var CartUpdateEventSettings
     */
    private $cartUpdateEventSettings;

    /**
     * @param CheckoutSession $checkoutSession
     * @param DataMapperResolver $dataMapperResolver
     * @param EventBuilderFactory $eventBuilderFactory
     * @param CartUpdateEventSettings $cartUpdateEventSettings
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        DataMapperResolver $dataMapperResolver,
        EventBuilderFactory $eventBuilderFactory,
        CartUpdateEventSettings $cartUpdateEventSettings
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dataMapperResolver = $dataMapperResolver;
        $this->eventBuilderFactory = $eventBuilderFactory;
        $this->cartUpdateEventSettings = $cartUpdateEventSettings;
    }

    /**
     * Returns cart update event
     *
     * @return array
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     * @throws NotFoundException
     */
    public function getEvents(): array
    {
        $quote = $this->getCurrentQuote();

        if (!$quote) {
            return [];
        }

        $body = $this->dataMapperResolver->map($quote, CartUpdateEvent::ENTITY_TYPE)->toArray();
        $body['action'] = $this->cartUpdateEventSettings->getCartAction();

        return $this->eventBuilderFactory->create(
            [
                'eventName' => CartUpdateEvent::ENTITY_TYPE,
                'eventBody' => $body
            ]
        )->build();
    }

    /**
     * Returns current quote
     *
     * @return CartInterface|null
     */
    private function getCurrentQuote(): ?CartInterface
    {
        try {
            return $this->checkoutSession->getQuote();
        } catch (LocalizedException | NoSuchEntityException $e) {
            return null;
        }
    }
}
