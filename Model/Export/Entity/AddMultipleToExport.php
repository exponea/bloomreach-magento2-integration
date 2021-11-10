<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Entity;

use Bloomreach\EngagementConnector\Model\ExportEntityModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity as ExportEntityResourceModel;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for adding the multiple entities' id to the export
 */
class AddMultipleToExport
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
     * Add multiple entities id to export
     *
     * @param string $entityType
     * @param array $exportEntityIds
     *
     * @throws LocalizedException
     */
    public function execute(string $entityType, array $exportEntityIds): void
    {
        $this->exportEntityResourceModel->insertMultipleRaws($this->prepareRaws($entityType, $exportEntityIds));
    }

    /**
     * Prepare raws
     *
     * @param string $entityType
     * @param array $exportEntityIds
     *
     * @return array
     */
    private function prepareRaws(string $entityType, array $exportEntityIds): array
    {
        $result = [];
        foreach ($exportEntityIds as $exportEntityId) {
            $result[] = [
                ExportEntityModel::EXPORT_ENTITY_ID => $exportEntityId,
                ExportEntityModel::ENTITY_TYPE => $entityType
            ];
        }

        return $result;
    }
}
