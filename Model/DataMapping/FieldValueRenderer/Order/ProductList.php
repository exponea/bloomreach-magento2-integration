<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Order;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * The class is responsible for rendering the product_ids field
 */
class ProductList implements RenderInterface
{
    /**
     * Render the value of order field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return array
     */
    public function render($entity, string $fieldCode)
    {
        $productList = [];

        /** @var OrderItemInterface[] $orderItems */
        $orderItems = $entity->getAllVisibleItems();

        foreach ($orderItems as $orderItem) {
            $productList[] = [
                'product_id' => $orderItem->getProductId(),
                'sku' => $this->getProductSku($orderItem),
                'quantity' => (int) $orderItem->getQtyOrdered()
            ];
        }

        return $productList;
    }

    /**
     * Returns product sku
     *
     * @param OrderItemInterface $orderItem
     *
     * @return string
     */
    private function getProductSku(OrderItemInterface $orderItem): string
    {
        $product = null;
        if (in_array($orderItem->getProductType(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE])) {
            $product = $orderItem->getProduct();
        }

        return $product ? $product->getSku() : $orderItem->getSku();
    }
}
