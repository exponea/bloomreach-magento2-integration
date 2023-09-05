<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api\Data;

/**
 * Initial Export Status Data Interface
 */
interface InitialExportStatusInterface
{
    /**
     * Get Entity Id
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set Entity Id
     *
     * @param int|string $entityId
     *
     * @return void
     */
    public function setEntityId($entityId): void;

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

    /**
     * Get Status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set Status
     *
     * @param int $statusCode
     *
     * @return void
     */
    public function setStatus(int $statusCode): void;

    /**
     * Get Total Items
     *
     * @return int
     */
    public function getTotalItems(): int;

    /**
     * Set Total Items
     *
     * @param int $totalItems
     *
     * @return void
     */
    public function setTotalItems(int $totalItems): void;

    /**
     * Get Total Exported
     *
     * @return int
     */
    public function getTotalExported(): int;

    /**
     * Set Total Exported
     *
     * @param int $totalExported
     *
     * @return void
     */
    public function setTotalExported(int $totalExported): void;

    /**
     * Get Total Error Items
     *
     * @return int
     */
    public function getTotalErrorItems(): int;

    /**
     * Set Total Error Items
     *
     * @param int $totalErrorItems
     *
     * @return void
     */
    public function setTotalErrorItems(int $totalErrorItems): void;

    /**
     * Get Errors
     *
     * @return string
     */
    public function getErrors(): string;

    /**
     * Set Errors
     *
     * @param string $errors
     *
     * @return void
     */
    public function setErrors(string $errors): void;

    /**
     * Get Is Locked
     *
     * @return bool
     */
    public function isLocked(): bool;

    /**
     * Set Is Locked
     *
     * @param bool $isLocked
     *
     * @return void
     */
    public function setIsLocked(bool $isLocked): void;

    /**
     * Get Started At
     *
     * @return string
     */
    public function getStartedAt(): string;

    /**
     * Set Started At
     *
     * @param string|null $startedAt
     *
     * @return void
     */
    public function setStartedAt(?string $startedAt): void;

    /**
     * Get Finished At
     *
     * @return string
     */
    public function getFinishedAt(): string;

    /**
     * Set Finished At
     *
     * @param string|null $finishedAt
     *
     * @return void
     */
    public function setFinishedAt(?string $finishedAt): void;
}
