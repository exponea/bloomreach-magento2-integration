<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Config\Source;

use Bloomreach\EngagementConnector\Model\DataMapping\ConfigResolver;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * 'catalog_product_variants' fields source
 */
class CatalogProductVariantsFields extends CatalogProductFields
{
    /**
     * Get entity type
     *
     * @return string
     */
    protected function getEntityType(): string
    {
        return ProductVariantsType::ENTITY_TYPE;
    }
}
