<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\ExportPreconfiguration;

/**
 * Contains result of export entity preconfiguration
 */
interface PreconfigurationResultInterface
{
    /**
     * Get Entity Name
     *
     * @return string
     */
    public function getEntityName(): string;

    /**
     * Set Entity Name
     *
     * @param string $entityName
     *
     * @return void
     */
    public function setEntityName(string $entityName): void;

    /**
     * Get Has Error
     *
     * @return bool
     */
    public function hasError(): bool;

    /**
     * Set Error
     *
     * @param bool $isError
     *
     * @return void
     */
    public function setError(bool $isError): void;

    /**
     * Get Body
     *
     * @return string
     */
    public function getBody(): string;

    /***
     * Set Body
     *
     * @param string $body
     *
     * @return void
     */
    public function setBody(string $body): void;
}
