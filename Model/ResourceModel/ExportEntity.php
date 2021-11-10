<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel;

use Bloomreach\EngagementConnector\Model\ExportEntityModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Export Entity Resource Model
 */
class ExportEntity extends AbstractDb
{
    private const TABLE_NAME = 'bloomreach_export_entity';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ExportEntityModel::ENTITY_ID);
    }

    /**
     * Delete by Entity ids
     *
     * @param array $entityIds
     *
     * @throws LocalizedException
     */
    public function deleteByEntityIds(array $entityIds): void
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [ExportEntityModel::ENTITY_ID . ' in (?)' => $entityIds]
        );
    }

    /**
     * Insert multiple raws to table
     *
     * @param array $raws
     *
     * @return void
     * @throws LocalizedException
     */
    public function insertMultipleRaws(array $raws): void
    {
        $connection = $this->getConnection();
        $connection->insertOnDuplicate(
            $this->getMainTable(),
            $raws
        );
    }
}
