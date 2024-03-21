<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\Order\OrderRepository;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * The class is responsible for rendering the value of order item field from order object
 */
class OrderField implements RenderInterface
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Render the value of order field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function render($entity, string $fieldCode)
    {
        $orderId = $entity->getOrderId();
        /** @var OrderInterface $order */
        $order = $orderId ? $this->orderRepository->getById((int) $orderId) : $entity->getOrder();

        return $order ? (string) $order->getData($fieldCode) : '';
    }
}
