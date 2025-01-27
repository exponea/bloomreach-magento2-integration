<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Product\Store;

use Bloomreach\EngagementConnector\Model\Product\CategoryDataResolver as GeneralCategoryDataResolver;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class is responsible for returning category data for specific store
 */
class CategoryDataResolver
{
    /**
     * @var GeneralCategoryDataResolver
     */
    private $categoryDataResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param GeneralCategoryDataResolver $categoryDataResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        GeneralCategoryDataResolver $categoryDataResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryDataResolver = $categoryDataResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Get category data by specific store ID
     *
     * @param Product $product
     * @param string $fieldCode
     * @param int|null $storeId
     *
     * @return array|string
     */
    public function execute(Product $product, string $fieldCode, ?int $storeId = null)
    {
        if ($storeId === null) {
            return $this->categoryDataResolver->getDataByCode($product, $fieldCode);
        }

        try {
            $currentStore = $this->storeManager->getStore();
            $this->storeManager->setCurrentStore($storeId);
            $result = $this->categoryDataResolver->getDataByCode($product, $fieldCode, $storeId);
            $this->storeManager->setCurrentStore($currentStore);

            return $result;
        } catch (Exception $e) {
            return $this->categoryDataResolver->getDataByCode($product, $fieldCode);
        }
    }
}
