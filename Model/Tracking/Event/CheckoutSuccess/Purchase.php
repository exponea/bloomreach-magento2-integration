<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Tracking\Event\CheckoutSuccess;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Tracking\Event\EventsInterface;
use Bloomreach\EngagementConnector\Model\Tracking\EventBuilderFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * The class is responsible for generating purchase events
 */
class Purchase implements ArgumentInterface, EventsInterface
{
    private const PURCHASE_EVENT = 'purchase';

    private const PURCHASE_ITEM_EVENT = 'purchase_item';

    private const FRONTEND_PURCHASE_EVENT_NAME = 'order';

    private const FRONTEND_PURCHASE_ITEM_EVENT_NAME = 'order_item';

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
     * @var RegisteredGenerator
     */
    private $registeredGenerator;

    /**
     * @param CheckoutSession $checkoutSession
     * @param DataMapperResolver $dataMapperResolver
     * @param EventBuilderFactory $eventBuilderFactory
     * @param RegisteredGenerator $registeredGenerator
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        DataMapperResolver $dataMapperResolver,
        EventBuilderFactory $eventBuilderFactory,
        RegisteredGenerator $registeredGenerator
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->dataMapperResolver = $dataMapperResolver;
        $this->eventBuilderFactory = $eventBuilderFactory;
        $this->registeredGenerator = $registeredGenerator;
    }

    /**
     * Returns list of purchase events
     *
     * @return array
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     */
    public function getEvents(): array
    {
        $events = [];
        $order = $this->getOrder();

        if ($order) {
            $events[] = $this->eventBuilderFactory
                ->create(
                    [
                        'eventName' => self::FRONTEND_PURCHASE_EVENT_NAME,
                        'eventBody' => $this->deleteUnusedFields(
                            $this->dataMapperResolver->map($order, self::PURCHASE_EVENT)->toArray()
                        )
                    ]
                )->build();

            $orderItems = $order->getAllVisibleItems();

            foreach ($orderItems as $orderItem) {
                $events[] = $this->eventBuilderFactory
                    ->create(
                        [
                            'eventName' => self::FRONTEND_PURCHASE_ITEM_EVENT_NAME,
                            'eventBody' => $this->deleteUnusedFields(
                                $this->dataMapperResolver->map($orderItem, self::PURCHASE_ITEM_EVENT)->toArray()
                            )
                        ]
                    )->build();
            }
        }

        return $events;
    }

    /**
     * Returns last order
     *
     * @return OrderInterface|null
     */
    private function getOrder(): ?OrderInterface
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    /**
     * Delete unused fields
     *
     * @param array $data
     *
     * @return void
     */
    private function deleteUnusedFields(array $data): array
    {
        $this->registeredGenerator->deleteRegisteredData($data);

        if (isset($data['timestamp'])) {
            unset($data['timestamp']);
        }

        return $data;
    }
}
