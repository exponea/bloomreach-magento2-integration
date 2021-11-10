<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Magento\Bundle\Model\Product\Type;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * The class is responsible to get stock level
 */
class StockLevel implements RenderInterface
{
    /**
     * @var StockRegistry
     */
    private $stock;

    /**
     * @param StockRegistry $stock
     */
    public function __construct(StockRegistry $stock)
    {
        $this->stock = $stock;
    }

    /**
     * Render the stock level
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return int
     */
    public function render($entity, string $fieldCode)
    {
        if (in_array($entity->getTypeId(), [Grouped::TYPE_CODE, Type::TYPE_CODE, Configurable::TYPE_CODE])) {
            return $this->getStockQty($entity);
        }

        return (int) $this->stock->getStockItem($entity->getId())->getQty();
    }

    /**
     * Get sum of qty of configurable, bundle, grouped products
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     *
     * @return int
     */
    private function getStockQty($entity): int
    {
        $typeInstance = $entity->getTypeInstance();
        $requiredChildrenIds = $typeInstance->getChildrenIds($entity->getId(), true);

        foreach ($requiredChildrenIds as $childrenIds) {
            foreach ($childrenIds as $childrenId) {
                $ids[] = $childrenId;
            }
        }

        if (empty($ids)) {
            return 0;
        }

        $stockQty = [];

        foreach ($ids as $id) {
            $stockQty[$id] = (int) $this->stock->getStockItem($id)->getQty();
        }

        return (int) array_sum($stockQty);
    }
}
