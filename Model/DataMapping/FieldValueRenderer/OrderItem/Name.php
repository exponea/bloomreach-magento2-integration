<?php

/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\OrderItem\ChildItems;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderItemInterface;
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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ChildItems $childItems
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ChildItems $childItems,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->childItems = $childItems;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Render the value of order item field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        $productId = $entity->getProductId();
        $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();

        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId, false, $defaultStoreId);
            } catch (NoSuchEntityException $e) {
            }
        }

        if ($product) {
            $name = $product->getName();
        } else {
            $name = $entity->getName();
        }

        if ($entity->getProductType() !== Configurable::TYPE_CODE) {
            return $name;
        }

        /** @var OrderItemInterface[] $childrenItems */
        $childrenItems = $entity->getChildrenItems();

        if (!$childrenItems) {
            // $childName = $this->childItems->getChildName((int) $entity->getItemId());
            $childProduct = $this->productRepository->getById($entity->getProductId(), false, $defaultStoreId);
            $childName = $childProduct->getName();
            return $childName ?: $name;
        }

        foreach ($childrenItems as $childrenItem) {
            $childrenItemProduct = $this->productRepository->getById($childrenItem->getProductId(), false, $defaultStoreId);
            return $childrenItemProduct->getName() ?: $name;
        }

        return $name;
    }
}
