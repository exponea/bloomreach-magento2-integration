<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\Product;

use Bloomreach\EngagementConnector\Model\DataMapping\FieldValueRenderer\RenderInterface;
use Bloomreach\EngagementConnector\Service\Product\GetParentProductByChildId;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Model\AbstractModel;

/**
 * The class is responsible for rendering the product_sku field
 */
class ProductSku implements RenderInterface
{
    /**
     * @var GetParentProductByChildId
     */
    private $getParentProductByChildId;

    /**
     * @param GetParentProductByChildId $getParentProductByChildId
     */
    public function __construct(
        GetParentProductByChildId $getParentProductByChildId
    ) {
        $this->getParentProductByChildId = $getParentProductByChildId;
    }

    /**
     * Render the product_id product value
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $fieldCode
     *
     * @return string
     */
    public function render($entity, string $fieldCode)
    {
        /** @var Product $entity */
        if ($entity->isVisibleInSiteVisibility()) {
            return '';
        }

        if ($parent = $this->getParentProductByChildId->execute((int) $entity->getEntityId(), $entity->getStoreId())) {
            return $parent->getSku();
        }

        return '';
    }
}
