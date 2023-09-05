<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 *
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Setup\Service;

use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatusResourceModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Remove db tables
 */
class RemoveTables
{
    /**
     * Removing db tables
     *
     * @param AdapterInterface $connection
     *
     * @return void
     */
    public function execute(AdapterInterface $connection): void
    {
        $tables = [
            InitialExportStatusResourceModel::TABLE_NAME,
            ExportQueue::TABLE_NAME
        ];

        foreach ($tables as $table) {
            $connection->dropTable($table);
        }
    }
}
