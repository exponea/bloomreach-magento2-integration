<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for obtaining child order items
 */
class ChildItems
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Returns an array of product ids
     *
     * @param int $orderItemId
     *
     * @return array
     */
    public function getChildIds(int $orderItemId): array
    {
        $result = $this->getConnection()->fetchCol($this->getSelect($orderItemId, [OrderItemInterface::PRODUCT_ID]));

        return $result ?: [];
    }

    /**
     * Returns connection
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }

    /**
     * Returns child item select
     *
     * @param int $orderItemId
     * @param array $columns
     *
     * @return Select
     */
    private function getSelect(int $orderItemId, array $columns): Select
    {
        $connection = $this->getConnection();
        $select = $connection->select()->reset();
        $select->from(
            $connection->getTableName('sales_order_item'),
            $columns
        )->where('parent_item_id = ?', $orderItemId);

        return $select;
    }

    /**
     * Get Child name
     *
     * @param int $orderItemId
     *
     * @return string
     */
    public function getChildName(int $orderItemId): string
    {
        return (string) $this->getConnection()
            ->fetchOne(
                $this->getSelect(
                    $orderItemId,
                    [OrderItemInterface::NAME]
                )
            );
    }

    /**
     * Returns data to calculate discount amount
     *
     * @param int $orderItemId
     *
     * @return array
     */
    public function getDiscountData(int $orderItemId): array
    {
        $select = $this->getSelect(
            $orderItemId,
            [
                OrderItemInterface::QTY_ORDERED,
                OrderItemInterface::BASE_DISCOUNT_AMOUNT,
                OrderItemInterface::DISCOUNT_AMOUNT
            ]
        );

        $result = $this->getConnection()->fetchAll($select);

        return $result ?: [];
    }
}
