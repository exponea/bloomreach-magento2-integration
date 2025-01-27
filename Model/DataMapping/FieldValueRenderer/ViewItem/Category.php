<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\ViewItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\Product\Store\CategoryDataResolver;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The class is responsible for rendering the value of product field related to the category
 */
class Category implements RenderInterface
{
    /**
     * @var CategoryDataResolver
     */
    private $categoryDataResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CategoryDataResolver $categoryDataResolver
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryDataResolver $categoryDataResolver,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryDataResolver = $categoryDataResolver;
        $this->storeManager = $storeManager;
    }

    /**
     * Render the value of product field
     *
     * @param Product $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return $this->categoryDataResolver->execute($entity, $fieldCode);
        }

        try {
            $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();

            return (int) $entity->getStoreId() === $defaultStoreId
                ? $this->categoryDataResolver->execute($entity, $fieldCode)
                : $this->categoryDataResolver->execute($entity, $fieldCode, $defaultStoreId);
        } catch (Exception $e) {
            return $this->categoryDataResolver->execute($entity, $fieldCode);
        }
    }
}
