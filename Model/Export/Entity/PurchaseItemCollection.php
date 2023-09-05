<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Entity;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;

/**
 * Collection model for Purchase item collection
 */
class PurchaseItemCollection extends Collection
{
    /**
     * Get the Order Items
     *
     * @return Collection
     */
    public function _beforeLoad()
    {
        $this->addTypeIdFilter();

        return parent::_beforeLoad();
    }

    /**
     * Adds parent id filter
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
     * Adds parent id filter
     *
     * @return void
     */
    private function addTypeIdFilter()
    {
        $this->addFieldToFilter(
            [
                OrderItemInterface::PARENT_ITEM_ID,
                OrderItemInterface::PARENT_ITEM_ID,
                OrderItemInterface::PARENT_ITEM_ID
            ],
            [
                ['null' => true],
                ['eq' => 0],
                ['eq' => '']
            ]
        );
    }
}
