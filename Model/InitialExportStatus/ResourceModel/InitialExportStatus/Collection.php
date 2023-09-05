<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatus;

use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus as InitialExportStatusModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatusResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Initial Export Status Collection
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
        $this->_init(InitialExportStatusModel::class, InitialExportStatusResourceModel::class);
    }
}
