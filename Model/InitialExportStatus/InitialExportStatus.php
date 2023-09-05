<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatusResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Model for Initial Export Status
 */
class InitialExportStatus extends AbstractModel implements InitialExportStatusInterface
{
    public const ENTITY_ID = 'entity_id';

    public const ENTITY_TYPE = 'entity_type';

    public const STATUS = 'status';

    public const TOTAL_ITEMS = 'total_items';

    public const TOTAL_EXPORTED = 'total_exported';

    public const TOTAL_ERROR_ITEMS = 'total_error_items';

    public const ERRORS = 'errors';

    public const IS_LOCKED = 'is_locked';

    public const STARTED_AT = 'started_at';

    public const FINISHED_AT = 'finished_at';

    /**
     * Get Entity Id
     *
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        $entityId = $this->getData(self::ENTITY_ID);

        return $entityId === null ? $entityId : (int) $entityId;
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

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param int $statusCode
     *
     * @return void
     */
    public function setStatus(int $statusCode): void
    {
        $this->setData(self::STATUS, $statusCode);
    }

    /**
     * Get Total Items
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return (int) $this->getData(self::TOTAL_ITEMS);
    }

    /**
     * Set Total Items
     *
     * @param int $totalItems
     *
     * @return void
     */
    public function setTotalItems(int $totalItems): void
    {
        $this->setData(self::TOTAL_ITEMS, $totalItems);
    }

    /**
     * Get Total Exported
     *
     * @return int
     */
    public function getTotalExported(): int
    {
        return (int) $this->getData(self::TOTAL_EXPORTED);
    }

    /**
     * Set Total Exported
     *
     * @param int $totalExported
     *
     * @return void
     */
    public function setTotalExported(int $totalExported): void
    {
        $this->setData(self::TOTAL_EXPORTED, $totalExported);
    }

    /**
     * Get Total Error Items
     *
     * @return int
     */
    public function getTotalErrorItems(): int
    {
        return (int) $this->getData(self::TOTAL_ERROR_ITEMS);
    }

    /**
     * Set Total Error Items
     *
     * @param int $totalErrorItems
     *
     * @return void
     */
    public function setTotalErrorItems(int $totalErrorItems): void
    {
        $this->setData(self::TOTAL_ERROR_ITEMS, $totalErrorItems);
    }

    /**
     * Get Errors
     *
     * @return string
     */
    public function getErrors(): string
    {
        return (string) $this->getData(self::ERRORS);
    }

    /**
     * Set Errors
     *
     * @param string $errors
     *
     * @return void
     */
    public function setErrors(string $errors): void
    {
        $this->setData(self::ERRORS, $errors);
    }

    /**
     * Get Is Locked
     *
     * @return bool
     */
    public function isLocked(): bool
    {
        return (bool) $this->getData(self::IS_LOCKED);
    }

    /**
     * Set Is Locked
     *
     * @param bool $isLocked
     *
     * @return void
     */
    public function setIsLocked(bool $isLocked): void
    {
        $this->setData(self::IS_LOCKED, $isLocked);
    }

    /**
     * Get Started At
     *
     * @return string
     */
    public function getStartedAt(): string
    {
        return (string) $this->getData(self::STARTED_AT);
    }

    /**
     * Set Started At
     *
     * @param string|null $startedAt
     *
     * @return void
     */
    public function setStartedAt(?string $startedAt): void
    {
        $this->setData(self::STARTED_AT, $startedAt);
    }

    /**
     * Get Finished At
     *
     * @return string
     */
    public function getFinishedAt(): string
    {
        return (string) $this->getData(self::FINISHED_AT);
    }

    /**
     * Set Finished At
     *
     * @param string|null $finishedAt
     *
     * @return void
     */
    public function setFinishedAt(?string $finishedAt): void
    {
        $this->setData(self::FINISHED_AT, $finishedAt);
    }

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(InitialExportStatusResourceModel::class);
    }
}
