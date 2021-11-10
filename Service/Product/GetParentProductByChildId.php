<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Product;

use Magento\Bundle\Model\Product\Type;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * The class responsible for getting the parent product by child id
 */
class GetParentProductByChildId
{
    /**
     * @var Configurable
     */
    private $configurableType;

    /**
     * @var Type
     */
    private $bundleType;

    /**
     * @var Grouped
     */
    private $groupedType;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @param Configurable $configurableType
     * @param Type $bundleType
     * @param Grouped $groupedType
     * @param ProductRepository $productRepository
     */
    public function __construct(
        Configurable $configurableType,
        Type $bundleType,
        Grouped $groupedType,
        ProductRepository $productRepository
    ) {
        $this->configurableType = $configurableType;
        $this->bundleType = $bundleType;
        $this->groupedType = $groupedType;
        $this->productRepository = $productRepository;
    }

    /**
     * Returns parent product by child id
     *
     * @param int $childProductId
     * @param int|null $storeId
     *
     * @return ProductInterface|null
     */
    public function execute(int $childProductId, ?int $storeId = null): ?ProductInterface
    {
        $parentId = '';
        $configurableIds = $this->configurableType->getParentIdsByChild($childProductId);

        if (count($configurableIds) > 0) {
            $parentId = $configurableIds[0];
        }

        $bundleIds = $this->bundleType->getParentIdsByChild($childProductId);

        if (count($bundleIds) > 0) {
            $parentId = $bundleIds[0];
        }

        $groupedIds = $this->groupedType->getParentIdsByChild($childProductId);

        if (count($groupedIds) > 0) {
            $parentId = $groupedIds[0];
        }

        if (!$parentId) {
            return null;
        }

        try {
            return $this->productRepository->getById($parentId, false, $storeId);
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}
