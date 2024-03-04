<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderFactory as OrderModelFactory;

/**
 * This resource model is responsible for getting simplified order object
 */
class Order
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var OrderModelFactory
     */
    private $orderModelFactory;

    /**
     * @param ResourceConnection $resourceConnection
     * @param OrderModelFactory $orderModelFactory
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        OrderModelFactory $orderModelFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderModelFactory = $orderModelFactory;
    }

    /**
     * Get order by id
     *
     * @param int $orderId
     *
     * @return OrderInterface
     */
    public function getById(int $orderId): OrderInterface
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()->reset()->from($connection->getTableName('sales_order'));
        $select->where('entity_id = ?', $orderId);
        $result = $connection->fetchRow($select);

        return $this->orderModelFactory->create(['data' => is_array($result) ? $result : []]);
    }
}
