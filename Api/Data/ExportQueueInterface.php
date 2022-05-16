<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api\Data;

/**
 * Export Queue Data Interface
 */
interface ExportQueueInterface
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
     * @return string|null
     */
    public function getEntityType(): ?string;

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
     * Get Status
     *
     * @param int|string $status
     *
     * @return void
     */
    public function setStatus($status): void;

    /**
     * Get Retries
     *
     * @return int
     */
    public function getRetries(): int;

    /**
     * Set Retries
     *
     * @param int|string $retires
     *
     * @return void
     */
    public function setRetries($retires): void;

    /**
     * Get Body
     *
     * @return string
     */
    public function getBody(): string;

    /**
     * Set Body
     *
     * @param string $body
     *
     * @return void
     */
    public function setBody(string $body): void;

    /**
     * Get Api Type
     *
     * @return string
     */
    public function getApiType(): string;

    /**
     * Set Api Type
     *
     * @param string $apiType
     *
     * @return void
     */
    public function setApiType(string $apiType): void;

    /**
     * Get Registered
     *
     * @return string
     */
    public function getRegistered(): string;

    /**
     * Set Registered
     *
     * @param string $registered
     *
     * @return void
     */
    public function setRegistered(string $registered): void;

    /**
     * Get failed sending attempts
     *
     * @return int
     */
    public function getFailedSendingAttempts(): int;

    /**
     * Set failed sending attempts
     *
     * @param int $failedAttempts
     *
     * @return void
     */
    public function setFailedSendingAttempts(int $failedAttempts): void;

    /**
     * Get time of the next sending attempt
     *
     * @return int
     */
    public function getTimeOfNextSendingAttempt(): int;

    /**
     * Set time of the next sending attempt
     *
     * @param int $time
     *
     * @return void
     */
    public function setTimeOfNextSendingAttempt(int $time): void;

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set Created At
     *
     * @param string $createdAt
     *
     * @return void
     */
    public function setCreatedAt(string $createdAt): void;

    /**
     * Get Updated At
     *
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt(string $updatedAt): void;
}
