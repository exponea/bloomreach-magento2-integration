<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Export Queue Resource Model
 */
class ExportQueue extends AbstractDb
{
    private const TABLE_NAME = 'bloomreach_export_queue';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ExportQueueModel::ENTITY_ID);
    }
}
