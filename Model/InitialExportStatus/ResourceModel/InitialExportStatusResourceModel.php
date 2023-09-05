<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel;

use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus as InitialExportStatusModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource model for Initial Export Status
 */
class InitialExportStatusResourceModel extends AbstractDb
{
    public const TABLE_NAME = 'bloomreach_initial_export_status';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, InitialExportStatusModel::ENTITY_ID);
    }
}
