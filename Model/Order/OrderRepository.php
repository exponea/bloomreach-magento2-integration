<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Order;

use Bloomreach\EngagementConnector\Model\ResourceModel\Order as OrderResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * This class is responsible for loading orders by ID
 * - It stores only one order in the local cache, unlike the OrderRepository
 * - It only returns data from `sales_order` table
 */
class OrderRepository
{
    /**
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var OrderInterface|null
     */
    private $cachedOrder;

    /**
     * @param OrderResource $orderResource
     */
    public function __construct(OrderResource $orderResource)
    {
        $this->orderResource = $orderResource;
    }

    /**
     * Get Order by ID
     *
     * @param int $orderId
     *
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $orderId): OrderInterface
    {
        if (($this->cachedOrder instanceof OrderInterface) && (int) $this->cachedOrder->getId() === $orderId) {
            return $this->cachedOrder;
        }

        $order = $this->orderResource->getById($orderId);

        if ($order->getId()) {
            $this->cachedOrder = $order;

            return $this->cachedOrder;
        }

        throw new NoSuchEntityException(
            __('The entity that was requested doesn\'t exist. Verify the entity and try again.')
        );
    }
}
