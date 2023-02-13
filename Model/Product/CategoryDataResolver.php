<?php

/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;


/**
 * The class is responsible for generating product category data
 */
class CategoryDataResolver
{
    private const CATEGORY_PATH = 'categories_path';

    private const CATEGORY_LEVEL = 'category_level_';

    private const CATEGORY_LEVEL_1 = 'category_level_1';

    private const CATEGORY_LEVEL_2 = 'category_level_2';

    private const CATEGORY_LEVEL_3 = 'category_level_3';

    private const CATEGORY_URL_1 = 'category_1_url';

    private const CATEGORY_URL_2 = 'category_2_url';

    private const CATEGORY_URL_3 = 'category_3_url';

    private const CATEGORY_IDS = 'categories_ids';

    private const CATEGORY_ID = 'category_id';

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var array
     */
    private $categoryCache = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Returns category data by code
     *
     * @param ProductInterface $product
     * @param string $code
     *
     * @return string|array
     */
    public function getDataByCode(ProductInterface $product, string $code)
    {
        $categoryId = $this->getProductFirstCategoryId($product);
        if (!$categoryId) {
            return '';
        }

        if (!array_key_exists($categoryId, $this->categoryCache)) {
            $this->generateCategoryData($categoryId);
        }

        return $this->categoryCache[$categoryId][$code] ?? '';
    }

    /**
     * Returns First Category Id
     *
     * @param ProductInterface $product
     *
     * @return false|mixed
     */
    private function getProductFirstCategoryId(ProductInterface $product): ?int
    {
        $defaultStoreId = $this->storeManager->getDefaultStoreView()->getId();
        $categoryIds = array_diff(
            $product->getCategoryIds(),
            [
                $this->storeManager->getStore($defaultStoreId)->getRootCategoryId()
            ]
        );
        $categoryId = (int) current(array_reverse($categoryIds));

        return $categoryId ?: null;
    }

    /**
     * Generate category data
     *
     * @param int $categoryId
     *
     * @return void
     */
    private function generateCategoryData(int $categoryId): void
    {
        $this->categoryCache[$categoryId] = [
            self::CATEGORY_PATH => '',
            self::CATEGORY_LEVEL_1 => '',
            self::CATEGORY_LEVEL_2 => '',
            self::CATEGORY_LEVEL_3 => '',
            self::CATEGORY_URL_1 => '',
            self::CATEGORY_URL_2 => '',
            self::CATEGORY_URL_3 => '',
            self::CATEGORY_IDS => '',
            self::CATEGORY_ID => $categoryId
        ];

        $category = $this->getCategory($categoryId);

        if (!$category) {
            return;
        }

        $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();

        if ($category->getStoreId() === 0) {
            // This workaround is required to correctly render the frontend URL for the current active store
            $category->setStoreId($defaultStoreId);
        }

        // It is a top level category if level equals 2
        if ((int) $category->getLevel() === 2) {
            $this->categoryCache[$categoryId][self::CATEGORY_PATH] = $category->getName();
            $this->categoryCache[$categoryId][self::CATEGORY_LEVEL_1] = $category->getName();
            $this->categoryCache[$categoryId][self::CATEGORY_URL_1] = $this->removeBaseRoot($category->getUrl());
            $this->categoryCache[$categoryId][self::CATEGORY_IDS] = [$category->getId()];
            return;
        }

        if ((int) $category->getLevel() === 3) {
            $this->generateCategoryDataWithLevel3($category, $defaultStoreId);
            return;
        }

        if ($category->getLevel() > 3) {
            $this->generateCategoryDataWithLevelMore3($category, $defaultStoreId);
        }
    }

    /**
     * Returns category
     *
     * @param int $categoryId
     *
     * @return CategoryInterface|null
     */
    private function getCategory(int $categoryId): ?CategoryInterface
    {
        try {
            // additionally make sure that category is retrieved for the defaul store only
            $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();
            return $this->categoryRepository->get($categoryId, $defaultStoreId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Remove redundant base root folder
     *
     * @param string $url
     *
     * @return string
     */
    private function removeBaseRoot(string $url): string
    {
        if (strpos($url, '/magento/') !== false) {
            $url = str_replace('/magento/', '/', $url);
        }

        return $url;
    }

    /**
     * Generate data for category with level = 3
     *
     * @param CategoryInterface $category
     * @param int $defaultStoreId
     *
     * @return void
     */
    private function generateCategoryDataWithLevel3(CategoryInterface $category, int $defaultStoreId): void
    {
        $parentCategory = $category->getParentCategory();
        // get parent category directly from category repository to make sure it uses default's store translations
        $parentCategoryDirect = $this->getCategory(intval($parentCategory->getId()));

        $categoryId = $category->getId();

        if ($parentCategory) {
            if ($parentCategory->getStoreId() === 0) {
                // This workaround is required to correctly render the frontend URL for the current active store
                $parentCategory->setStoreId($defaultStoreId);
            }
            $this->categoryCache[$categoryId][self::CATEGORY_LEVEL_1] = $parentCategoryDirect->getName();
            $this->categoryCache[$categoryId][self::CATEGORY_URL_1] = $this->removeBaseRoot(
                $parentCategory->getUrl()
            );
            $path[] = $parentCategoryDirect->getName();

            $ids[] = $parentCategory->getId();
        }

        $this->categoryCache[$categoryId][self::CATEGORY_LEVEL_2] = $category->getName();
        $this->categoryCache[$categoryId][self::CATEGORY_URL_2] = $this->removeBaseRoot($category->getUrl());
        $path[] = $parentCategoryDirect->getName();
        $ids[] = $category->getId();
        $this->categoryCache[$categoryId][self::CATEGORY_PATH] = $this->generatePath($path);
        $this->categoryCache[$categoryId][self::CATEGORY_IDS] = $ids;
    }

    /**
     * Generate category path
     *
     * @param array $path
     *
     * @return string
     */
    private function generatePath(array $path): string
    {
        return implode('>', $path);
    }

    /**
     * Generate data for category with level > 3
     *
     * @param CategoryInterface $category
     * @param int $defaultStoreId
     *
     * @return void
     */
    private function generateCategoryDataWithLevelMore3(CategoryInterface $category, int $defaultStoreId): void
    {
        $categoryId = $category->getId();
        $parentCategories = $category->getParentCategories();
        $this->categoryCache[$categoryId][self::CATEGORY_LEVEL_3] = $category->getName();
        $this->categoryCache[$categoryId][self::CATEGORY_URL_3] = $this->removeBaseRoot($category->getUrl());

        $iterator = 1;
        foreach ($parentCategories as $parentCategory) {
            if ($iterator > 2) {
                break;
            }
            // get parent category directly from category repository to make sure it uses default's store translations
            $parentCategoryDirect = $this->getCategory(intval($parentCategory->getId()));

            // $parentCategory->setStoreId($defaultStoreId);
            if ($parentCategory->getStoreId() === 0) {
                // This workaround is required to correctly render the frontend URL for the current active store
                $parentCategory->setStoreId($defaultStoreId);
            }

            $path[] = $parentCategoryDirect->getName();
            $ids[] = $parentCategory->getId();
            $this->categoryCache[$categoryId][self::CATEGORY_LEVEL . $iterator] = $parentCategoryDirect->getName();
            $this->categoryCache[$categoryId]['category_' . $iterator . '_url'] = $this->removeBaseRoot(
                $parentCategory->getUrl()
            );
            $iterator++;
        }
        $ids[] = $category->getId();
        $path[] = $parentCategoryDirect->getName();
        $this->categoryCache[$categoryId][self::CATEGORY_PATH] = $this->generatePath($path);
        $this->categoryCache[$categoryId][self::CATEGORY_IDS] = $ids;
    }
}
