<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem\ChildItems;
use Exception;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The class is responsible for rendering the value of order item name field
 */
class Name implements RenderInterface
{
    /**
     * @var ChildItems
     */
    private $childItems;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param ChildItems $childItems
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ChildItems $childItems,
        StoreManagerInterface $storeManager,
        ProductRepository $productRepository
    ) {
        $this->childItems = $childItems;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * Render the value of order item field
     *
     * @param Item $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return $this->getName($entity);
        }

        $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();

        if ($defaultStoreId === (int) $entity->getStoreId()) {
            return $this->getName($entity);
        }

        try {
            return $this->getNameForStore($entity, $defaultStoreId);
        } catch (Exception $e) {
            return $this->getName($entity);
        }
    }

    /**
     * Get product name
     *
     * @param OrderItemInterface $orderItem
     *
     * @return string
     */
    private function getName(OrderItemInterface $orderItem): string
    {
        $name = $orderItem->getName();

        if ($orderItem->getProductType() !== Configurable::TYPE_CODE) {
            return $name;
        }

        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $orderItem->getChildrenItems();

        if (!$childrenItems) {
            $childName = $this->childItems->getChildName(
                (int) $orderItem->getOrderId(),
                (int) $orderItem->getItemId()
            );

            return $childName ?: $name;
        }

        foreach ($childrenItems as $childrenItem) {
            return $childrenItem->getName();
        }

        return $name;
    }

    /**
     * Get name for store
     *
     * @param OrderItemInterface $orderItem
     * @param int $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getNameForStore(OrderItemInterface $orderItem, int $storeId): string
    {
        if ($orderItem->getProductType() !== Configurable::TYPE_CODE) {
            return $this->getProductName((int) $orderItem->getProductId(), $storeId);
        }

        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $orderItem->getChildrenItems() ?? [];

        if (!$childrenItems) {
            $childIds = $this->childItems->getChildIds((int) $orderItem->getOrderId(), (int) $orderItem->getItemId());

            foreach ($childIds as $childId) {
                return $this->getProductName((int) $childId, $storeId);
            }
        }

        foreach ($childrenItems as $childrenItem) {
            return $this->getProductName((int) $childrenItem->getProductId(), $storeId);
        }

        return $this->getProductName((int) $orderItem->getProductId(), $storeId);
    }

    /**
     * Get product name
     *
     * @param int $productId
     * @param int $storeId
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getProductName(int $productId, int $storeId): string
    {
        $name = $this->productRepository->getById($productId, false, $storeId)->getName();
        $this->productRepository->cleanCache();

        return $name;
    }
}
