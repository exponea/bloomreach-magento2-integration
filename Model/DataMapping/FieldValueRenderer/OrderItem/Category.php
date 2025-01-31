<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\Product\Store\CategoryDataResolver;
use Exception;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

/**
 * The class is responsible for rendering the value of order item product category field
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
     * Render the value of order item product category field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        /** @var Product $product */
        $product = $entity->getProduct();

        if (!$product) {
            return '';
        }

        if ($this->storeManager->isSingleStoreMode()) {
            return $this->categoryDataResolver->execute($product, $fieldCode);
        }

        try {
            $defaultStoreId = (int) $this->storeManager->getDefaultStoreView()->getId();

            return (int) $entity->getStoreId() === $defaultStoreId
                ? $this->categoryDataResolver->execute($product, $fieldCode)
                : $this->categoryDataResolver->execute($product, $fieldCode, $defaultStoreId);
        } catch (Exception $e) {
            return $this->categoryDataResolver->execute($product, $fieldCode);
        }
    }
}
