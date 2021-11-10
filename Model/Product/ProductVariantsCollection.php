<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as SimpleType;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Downloadable\Model\Product\Type;

/**
 * Collection model for product variants
 */
class ProductVariantsCollection extends Collection
{
    /**
     * Get the the product variants
     *
     * @return Collection
     */
    public function _beforeLoad()
    {
        $this->addAttributeToFilter(ProductInterface::TYPE_ID, ['in' => [
            SimpleType::TYPE_SIMPLE,
            SimpleType::TYPE_VIRTUAL,
            Type::TYPE_DOWNLOADABLE
        ]]);

        return parent::_beforeLoad();
    }
}
