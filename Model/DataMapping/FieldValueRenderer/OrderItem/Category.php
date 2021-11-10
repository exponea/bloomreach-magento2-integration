<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\OrderItem;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\Product\CategoryDataResolver;
use Bloomreach\EngagementConnector\Service\EavAttribute\GetAttributeValue;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

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
     * @param CategoryDataResolver $categoryDataResolver
     */
    public function __construct(CategoryDataResolver $categoryDataResolver)
    {
        $this->categoryDataResolver = $categoryDataResolver;
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

        return $product ? $this->categoryDataResolver->getDataByCode($product, $fieldCode) : '';
    }
}
