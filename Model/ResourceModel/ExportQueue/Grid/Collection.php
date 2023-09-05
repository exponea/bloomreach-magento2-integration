<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Grid;

use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Initial Export Queue Collection
 */
class Collection extends SearchResult
{
    /**
     * Override _initSelect
     *
     * @return void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap(ExportQueueModel::ENTITY_ID, 'main_table.entity_id');
        $this->addFieldToFilter(ExportQueueModel::API_TYPE, ['neq' => AddInitialExportDataToExportQueue::API_TYPE]);
        parent::_initSelect();
    }
}
