<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\OrderItem;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

/**
 * The class is responsible for obtaining child order items
 */
class GetChildItems
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $itemsCollectionCache = [];

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Returns array with child items
     *
     * @param OrderItemInterface $orderItem
     *
     * @return OrderItemInterface[]
     */
    public function execute(OrderItemInterface $orderItem): array
    {
        $orderItems = $orderItem->getChildrenItems();

        return $orderItems ?: $this->getChildItemsCollection((int) $orderItem->getItemId());
    }

    /**
     * Returns array with child items
     *
     * @param int $parentId
     *
     * @return OrderItemInterface[]
     */
    private function getChildItemsCollection(int $parentId): array
    {
        if (!array_key_exists($parentId, $this->itemsCollectionCache)) {
            // Clear cache
            $this->itemsCollectionCache = [];

            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter(OrderItemInterface::PARENT_ITEM_ID, $parentId);

            $this->itemsCollectionCache[$parentId] = $collection->getItems();
        }

        return $this->itemsCollectionCache[$parentId];
    }
}
