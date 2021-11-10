<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model;

use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity as ExportEntityResourceModel;
use Bloomreach\EngagementConnector\Api\Data\ExportEntityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Model for export entity
 */
class ExportEntityModel extends AbstractModel implements ExportEntityInterface
{
    const ENTITY_ID = 'entity_id';

    const EXPORT_ENTITY_ID = 'export_entity_id';

    const ENTITY_TYPE = 'entity_type';

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ExportEntityResourceModel::class);
    }

    /**
     * Get Entity Id
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return (int) $this->getData(self::ENTITY_ID);
    }

    /**
     * Set Entity Id
     *
     * @param int|string $entityId
     *
     * @return void
     */
    public function setEntityId($entityId): void
    {
        $this->setData(self::ENTITY_ID, (int) $entityId);
    }

    /**
     * Get Export Entity Id
     *
     * @return int
     */
    public function getExportEntityId(): int
    {
        return (int) $this->getData(self::EXPORT_ENTITY_ID);
    }

    /**
     * Set Export Entity Id
     *
     * @param int|string $exportEntityId
     *
     * @return void
     */
    public function setExportEntityId($exportEntityId): void
    {
        $this->setData(self::EXPORT_ENTITY_ID, (int) $exportEntityId);
    }

    /**
     * Get Entity Type
     *
     * @return string
     */
    public function getEntityType(): string
    {
        return (string) $this->getData(self::ENTITY_TYPE);
    }

    /**
     * Set Entity Type
     *
     * @param string $entityType
     *
     * @return void
     */
    public function setEntityType(string $entityType): void
    {
        $this->setData(self::ENTITY_TYPE, $entityType);
    }
}
