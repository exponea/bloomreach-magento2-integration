<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api\Data;

/**
 * Export Entity Data Interface
 */
interface ExportEntityInterface
{
    /**
     * Get Entity Id
     *
     * @return int
     */
    public function getEntityId(): int;

    /**
     * Set Entity Id
     *
     * @param int|string $entityId
     *
     * @return void
     */
    public function setEntityId($entityId): void;

    /**
     * Get Export Entity Id
     *
     * @return int
     */
    public function getExportEntityId(): int;

    /**
     * Set Export Entity Id
     *
     * @param int|string $exportEntityId
     *
     * @return void
     */
    public function setExportEntityId($exportEntityId): void;

    /**
     * Get Entity Type
     *
     * @return string
     */
    public function getEntityType(): string;

    /**
     * Set Entity Type
     *
     * @param string $entityType
     *
     * @return void
     */
    public function setEntityType(string $entityType): void;
}
