<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ExportPreconfiguration;

use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Contains result of export entity preconfiguration
 */
class PreconfigurationResult extends AbstractSimpleObject implements PreconfigurationResultInterface
{
    private const ENTITY_NAME = 'entity_name';

    private const ERROR = 'error';

    private const BODY = 'body';

    /**
     * Get Entity Name
     *
     * @return string
     */
    public function getEntityName(): string
    {
        return (string) $this->_get(self::ENTITY_NAME);
    }

    /**
     * Set Entity Name
     *
     * @param string $entityName
     *
     * @return void
     */
    public function setEntityName(string $entityName): void
    {
        $this->setData(self::ENTITY_NAME, $entityName);
    }

    /**
     * Get Has Error
     *
     * @return bool
     */
    public function hasError(): bool
    {
        return (bool) $this->_get(self::ERROR);
    }

    /**
     * Set Error
     *
     * @param bool $isError
     *
     * @return void
     */
    public function setError(bool $isError): void
    {
        $this->setData(self::ERROR, $isError);
    }

    /**
     * Get Body
     *
     * @return string
     */
    public function getBody(): string
    {
        return (string) $this->_get(self::BODY);
    }

    /***
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
}
