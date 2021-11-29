<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Quote;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * The class is responsible for rendering the product_ids field
 */
class ProductList implements RenderInterface
{
    /**
     * Render the value of quote field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return array
     */
    public function render($entity, string $fieldCode)
    {
        $productList = [];

        /** @var CartItemInterface[] $orderItems */
        $quoteItems = $entity->getAllVisibleItems();

        foreach ($quoteItems as $quoteItem) {
            $productList[] = [
                'product_id' => $quoteItem->getProductId(),
                'sku' => $this->getProductSku($quoteItem),
                'quantity' => (int) $quoteItem->getQty()
            ];
        }

        return $productList;
    }

    /**
     * Returns product sku
     *
     * @param CartItemInterface $quoteItem
     *
     * @return string
     */
    private function getProductSku(CartItemInterface $quoteItem): string
    {
        $product = null;

        if (in_array($quoteItem->getProductType(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE])) {
            $product = $quoteItem->getProduct();
        }

        return $product ? $product->getData('sku') : $quoteItem->getSku();
    }
}
