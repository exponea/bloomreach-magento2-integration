<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem;

use Magento\Catalog\Api\Data\ProductInterface;
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
     * @param int $orderId
     * @param int $orderItemId
     *
     * @return array
     */
    public function getChildIds(int $orderId, int $orderItemId): array
    {
        $result = $this->getConnection()->fetchCol($this->getSelect($orderId, $orderItemId, [OrderItemInterface::PRODUCT_ID]));

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
     * @param int $orderId
     * @param int $orderItemId
     * @param array $columns
     *
     * @return Select
     */
    private function getSelect(int $orderId, int $orderItemId, array $columns): Select
    {
        $connection = $this->getConnection();
        $select = $connection->select()->reset();
        $select->from(
            $connection->getTableName('sales_order_item'),
            $columns
        )->where('order_id = ?', $orderId)->where('parent_item_id = ?', $orderItemId);

        return $select;
    }

    /**
     * Get Child name
     *
     * @param int $orderId
     * @param int $orderItemId
     *
     * @return string
     */
    public function getChildName(int $orderId, int $orderItemId): string
    {
        return (string) $this->getConnection()
            ->fetchOne(
                $this->getSelect(
                    $orderId,
                    $orderItemId,
                    [OrderItemInterface::NAME]
                )
            );
    }

    /**
     * Returns data to calculate discount amount
     *
     * @param int $orderId
     * @param int $orderItemId
     *
     * @return array
     */
    public function getDiscountData(int $orderId, int $orderItemId): array
    {
        $select = $this->getSelect(
            $orderId,
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

    /**
     * Get product id by child Sku
     *
     * @param string $productSku
     *
     * @return string
     */
    public function getProductIdBySku(string $productSku): string
    {
        $select = $this->getConnection()->select()->reset();
        $select->from($this->getConnection()->getTableName('catalog_product_entity'), ['entity_id']);
        $select->where(ProductInterface::SKU .' = ?', $productSku);

        return (string) $this->getConnection()->fetchOne($select);
    }
}
