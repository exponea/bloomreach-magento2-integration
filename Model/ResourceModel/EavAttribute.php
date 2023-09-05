<?php

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Api\Data\AttributeInterface;

/**
 * The class is responsible for retrieving attribute id
 */
class EavAttribute
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var array
     */
    private $attributeIds = [];

    /**
     * @var array
     */
    private $entityTypeIds = [];

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get Attribute ID
     *
     * @param string $entityType
     * @param string $attributeCode
     *
     * @return int
     */
    public function getAttributeId(string $entityType, string $attributeCode): int
    {
        $entityTypeId = $this->getEntityTypeId($entityType);

        if (!isset($this->attributeIds[$entityTypeId][$attributeCode])) {
            $select = $this->getConnection()->select()->reset();
            $select->from(
                $this->getConnection()->getTableName('eav_attribute'),
                [AttributeInterface::ATTRIBUTE_ID]
            );
            $select->where(AttributeInterface::ENTITY_TYPE_ID . ' = ?', $entityTypeId);
            $select->where(AttributeInterface::ATTRIBUTE_CODE . ' = ?', $attributeCode);
            $this->attributeIds[$entityTypeId][$attributeCode] = (int) $this->getConnection()->fetchOne($select);
        }

        return $this->attributeIds[$entityTypeId][$attributeCode];
    }

    /**
     * Get Entity Type ID
     *
     * @param string $entityType
     *
     * @return int
     */
    private function getEntityTypeId(string $entityType): int
    {
        if (!array_key_exists($entityType, $this->entityTypeIds)) {
            $select = $this->getConnection()->select()->reset();
            $select->from(
                $this->getConnection()->getTableName('eav_entity_type'),
                ['entity_type_id']
            );
            $select->where('entity_type_code = ?', $entityType);
            $this->entityTypeIds[$entityType] = (int) $this->getConnection()->fetchOne($select);
        }

        return $this->entityTypeIds[$entityType];
    }

    /**
     * Get Connection
     *
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if ($this->connection === null) {
            $this->connection = $this->resourceConnection->getConnection();
        }

        return $this->connection;
    }
}
