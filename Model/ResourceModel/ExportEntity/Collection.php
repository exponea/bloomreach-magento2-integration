<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity;

use Bloomreach\EngagementConnector\Model\ExportEntityModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity as ExportEntityResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Export Entity Collection
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
        $this->_init(ExportEntityModel::class, ExportEntityResourceModel::class);
    }
}
