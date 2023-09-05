<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api\Data;

/**
 * Api Response Data Interface
 */
interface ResponseInterface
{
    /**
     * Get API response data
     *
     * @return array|null
     */
    public function getData(): ?array;

    /**
     * Set API response data as array
     *
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data = []);

    /**
     * Get response status code
     *
     * @return int|null
     */
    public function getStatusCode(): ?int;

    /**
     * Set response status code
     *
     * @param int $code
     *
     * @return void
     */
    public function setStatusCode(int $code);

    /**
     * Get response error text
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string;

    /**
     * Set response error text
     *
     * @param string $errorMessage
     *
     * @return void
     */
    public function setErrorMessage(string $errorMessage);
}
