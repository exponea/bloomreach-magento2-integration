<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Data;

use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;

/**
 * API Response Data
 */
class ResponseData implements ResponseInterface
{
    /**
     * Response data array
     *
     * @var array
     */
    private $data = [];

    /**
     * Response code
     *
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * Get API response data
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * Set API response data as array
     *
     * @param array $data
     *
     * @return void
     */
    public function setData(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get response status code
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->code;
    }

    /**
     * Set response status code
     *
     * @param int $code
     *
     * @return void
     */
    public function setStatusCode(int $code)
    {
        $this->code = $code;
    }

    /**
     * Get response error text
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Set response error text
     *
     * @param string $errorMessage
     *
     * @return void
     */
    public function setErrorMessage(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }
}
