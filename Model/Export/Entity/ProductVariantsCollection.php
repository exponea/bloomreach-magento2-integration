<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Entity;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Downloadable\Model\Product\Type as DownloadableType;

/**
 * Collection model for product variants
 */
class ProductVariantsCollection extends Collection
{
    public const VARIANT_TYPES = [
        Type::TYPE_SIMPLE,
        Type::TYPE_VIRTUAL,
        DownloadableType::TYPE_DOWNLOADABLE
    ];

    /**
     * Get the product variants
     *
     * @return Collection
     */
    public function _beforeLoad()
    {
        $this->addTypeIdFilter();

        return parent::_beforeLoad();
    }

    /**
     * Adds type id filter
     *
     * @return $this
     */
    protected function _renderFilters()
    {
        parent::_renderFilters();
        $this->addTypeIdFilter();

        return $this;
    }

    /**
     * Adds type id filter
     *
     * @return void
     */
    private function addTypeIdFilter()
    {
        $this->addAttributeToFilter(ProductInterface::TYPE_ID, ['in' => self::VARIANT_TYPES]);
    }
}
