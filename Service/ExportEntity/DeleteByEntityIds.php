<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\ExportEntity;

use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity as ExportEntityResourceModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for deleting multiple records by entity ids
 */
class DeleteByEntityIds
{
    /**
     * @var ExportEntityResourceModel
     */
    private $exportEntityResourceModel;

    /**
     * @param ExportEntityResourceModel $exportEntityResourceModel
     */
    public function __construct(ExportEntityResourceModel $exportEntityResourceModel)
    {
        $this->exportEntityResourceModel = $exportEntityResourceModel;
    }

    /**
     * Delete multiple records by entity ids
     *
     * @param array $entityIds
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $entityIds): void
    {
        $this->exportEntityResourceModel->deleteByEntityIds($entityIds);
    }
}
