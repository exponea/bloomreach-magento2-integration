<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Export Queue Collection
 */
class Collection extends AbstractCollection
{
    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ExportQueueModel::class, ExportQueueResourceModel::class);
    }
}
