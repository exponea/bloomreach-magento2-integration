<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel;

use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Expr;

/**
 * Export Queue Resource Model
 */
class ExportQueue extends AbstractDb
{
    public const TABLE_NAME = 'bloomreach_export_queue';

    /**
     * Delete by entity type
     *
     * @param string $entityType
     *
     * @return void
     * @throws LocalizedException
     */
    public function deleteByEntityType(string $entityType): void
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [ExportQueueModel::ENTITY_TYPE . ' = ?' => $entityType]
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ExportQueueModel::ENTITY_ID);
    }

    /**
     * Get count status list for the last hour items except csv export related:
     *  - key - status,
     *  - value - status count.
     *
     * @return array
     * @throws LocalizedException
     */
    public function getLastHourNonCsvItemsStatusCountList(): array
    {
        $connection = $this->getConnection();
        if ($connection === false) {
            return [];
        }

        $select = $connection->select();
        $select->from(
            $this->getMainTable(),
            [
                ExportQueueModel::STATUS,
                new Zend_Db_Expr('COUNT(' . ExportQueueModel::STATUS . ')')
            ]
        );
        $select->where(new Zend_Db_Expr(ExportQueueModel::UPDATED_AT . ' >= (NOW() - INTERVAL 1 HOUR)'));
        $select->where(ExportQueueModel::API_TYPE . ' <> ?', AddInitialExportDataToExportQueue::API_TYPE);
        $select->group(ExportQueueModel::STATUS);

        return $connection->fetchPairs($select);
    }

    /**
     * Update entities
     * - update status --> NEW
     * - update retries = 0
     *
     * @param array $entityIds
     *
     * @return int
     * @throws LocalizedException
     */
    public function updateStatusAndRetries(array $entityIds): int
    {
        $connection = $this->getConnection();

        if ($connection === false) {
            return 0;
        }

        return (int) $connection->update(
            $this->getMainTable(),
            [
                ExportQueueModel::STATUS => ExportQueueModel::STATUS_NEW,
                ExportQueueModel::RETRIES => 0
            ],
            [ExportQueueModel::ENTITY_ID . ' in(?)' => [$entityIds]]
        );
    }
}
