<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Model\Product\CategoryDataResolver;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

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
     * @param CategoryDataResolver $categoryDataResolver
     */
    public function __construct(CategoryDataResolver $categoryDataResolver)
    {
        $this->categoryDataResolver = $categoryDataResolver;
    }

    /**
     * Render the value of product field
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        return $this->categoryDataResolver->getDataByCode($entity, $fieldCode);
    }
}
