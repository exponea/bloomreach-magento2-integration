<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * The class is responsible for obtain and collect product children ids
 */
class ChildIdsDataProvider
{
    /**
     * @var array
     */
    private $childIds = [];

    /**
     * Returns product children ids
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getIds(ProductInterface $product): array
    {
        $productId = $product->getId();

        if (array_key_exists($productId, $this->childIds)) {
            return $this->childIds[$productId];
        }

        return $this->getChildrenIds($product);
    }

    /**
     * Retrieve product children ids
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getChildrenIds(ProductInterface $product): array
    {
        $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());

        return array_merge([], ...$childrenIds);
    }

    /**
     * Collect product children ids
     *
     * @param ProductInterface $product
     *
     * @return void
     */
    public function collectIds(ProductInterface $product): void
    {
        //Clear cache
        $this->childIds = [];

        //Collect ids
        $this->childIds[$product->getId()] = $this->getChildrenIds($product);
    }
}
