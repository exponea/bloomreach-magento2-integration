<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResourceModel;
use Magento\Framework\Model\AbstractModel;

/**
 * Model for export queue
 */
class ExportQueueModel extends AbstractModel implements ExportQueueInterface
{
    public const ENTITY_ID = 'entity_id';

    public const ENTITY_TYPE = 'entity_type';

    public const STATUS = 'status';

    public const RETRIES = 'retries';

    public const BODY = 'body';

    public const API_TYPE = 'api_type';

    public const REGISTERED = 'registered';

    public const FAILED_SENDING_ATTEMPTS = 'failed_sending_attempts';

    public const TIME_OF_NEXT_SENDING_ATTEMPT = 'time_of_next_sending_attempt';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    public const STATUS_NEW = 1;

    public const STATUS_IN_PROGRESS = 2;

    public const STATUS_ERROR = 3;

    public const STATUS_COMPLETE = 4;

    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ExportQueueResourceModel::class);
    }

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
        return (int) ($this->getData(self::STATUS) ?: self::STATUS_NEW);
    }

    /**
     * Get Status
     *
     * @param int|string $status
     *
     * @return void
     */
    public function setStatus($status): void
    {
        $this->setData(self::STATUS, (int) $status);
    }

    /**
     * Get Retries
     *
     * @return int
     */
    public function getRetries(): int
    {
        return (int) $this->getData(self::RETRIES);
    }

    /**
     * Set Retries
     *
     * @param int|string $retires
     *
     * @return void
     */
    public function setRetries($retires): void
    {
        $this->setData(self::RETRIES, $retires);
    }

    /**
     * Get Body
     *
     * @return string
     */
    public function getBody(): string
    {
        return (string) $this->getData(self::BODY);
    }

    /**
     * Set Body
     *
     * @param string $body
     *
     * @return void
     */
    public function setBody(string $body): void
    {
        $this->setData(self::BODY, $body);
    }

    /**
     * Get Api Type
     *
     * @return string
     */
    public function getApiType(): string
    {
        return (string) $this->getData(self::API_TYPE);
    }

    /**
     * Set Api Type
     *
     * @param string $apiType
     *
     * @return void
     */
    public function setApiType(string $apiType): void
    {
        $this->setData(self::API_TYPE, $apiType);
    }

    /**
     * Get Registered
     *
     * @return string
     */
    public function getRegistered(): string
    {
        return (string) $this->getData(self::REGISTERED);
    }

    /**
     * Set Registered
     *
     * @param string $registered
     *
     * @return void
     */
    public function setRegistered(string $registered): void
    {
        $this->setData(self::REGISTERED, $registered);
    }

    /**
     * Get failed sending attempts
     *
     * @return int
     */
    public function getFailedSendingAttempts(): int
    {
        return (int) $this->getData(self::FAILED_SENDING_ATTEMPTS);
    }

    /**
     * Set failed sending attempts
     *
     * @param int $failedAttempts
     *
     * @return void
     */
    public function setFailedSendingAttempts(int $failedAttempts): void
    {
        $this->setData(self::FAILED_SENDING_ATTEMPTS, $failedAttempts);
    }

    /**
     * Get time of the next sending attempt
     *
     * @return int
     */
    public function getTimeOfNextSendingAttempt(): int
    {
        return (int) $this->getData(self::TIME_OF_NEXT_SENDING_ATTEMPT);
    }

    /**
     * Set time of the next sending attempt
     *
     * @param int $time
     *
     * @return void
     */
    public function setTimeOfNextSendingAttempt(int $time): void
    {
        $this->setData(self::TIME_OF_NEXT_SENDING_ATTEMPT, $time);
    }

    /**
     * Get Created At
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param string $createdAt
     *
     * @return void
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Updated At
     *
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return (string) $this->getData(self::UPDATED_AT);
    }

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt(string $updatedAt): void
    {
        $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
