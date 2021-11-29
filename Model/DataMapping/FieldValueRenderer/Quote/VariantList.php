<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Quote;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * The class is responsible for rendering the variant_list field
 */
class VariantList implements RenderInterface
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
        $variantList = [];

        /** @var CartItemInterface[] $orderItems */
        $quoteItems = $entity->getAllVisibleItems();

        foreach ($quoteItems as $quoteItem) {
            if ($quoteItem->getChildren()) {
                $variantList[] = $this->getChildrenItemData($quoteItem);
            } else {
                $variantList[] = [$this->getItemData($quoteItem)];
            }
        }

        return array_merge([], ...$variantList);
    }

    /**
     * Returns children items data
     *
     * @param CartItemInterface $quoteItem
     *
     * @return array
     */
    private function getChildrenItemData(CartItemInterface $quoteItem): array
    {
        $variantList = [];
        /** @var CartItemInterface[] $childrenItems */
        $childrenItems = $quoteItem->getChildren();

        foreach ($childrenItems as $childrenItem) {
            $variantList[] = $this->getItemData($childrenItem);
        }

        return $variantList;
    }

    /**
     * Returns item Data
     *
     * @param CartItemInterface $quoteItem
     *
     * @return array
     */
    private function getItemData(CartItemInterface $quoteItem): array
    {
        return [
            'variant_id' => $quoteItem->getProductId(),
            'sku' => $quoteItem->getSku(),
            'quantity' => $this->getItemQty($quoteItem)
        ];
    }

    /**
     * Get item quantity
     *
     * @param CartItemInterface $quoteItem
     *
     * @return float
     */
    private function getItemQty(CartItemInterface $quoteItem): float
    {
        $qty = $quoteItem->getQty();

        if ($quoteItem->getParentItem() && $quoteItem->getParentItem()->getProductType() === Configurable::TYPE_CODE) {
            $qty = $quoteItem->getParentItem()->getQty();
        }

        return (float) $qty;
    }
}
