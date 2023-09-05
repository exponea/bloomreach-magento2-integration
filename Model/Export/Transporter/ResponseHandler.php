<?php

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter;

use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;
use Bloomreach\EngagementConnector\Exception\ExportRequestException;

/**
 * The class is responsible for handling export response
 */
class ResponseHandler
{
    private const STATUS_OK = 200;

    private const TOO_MANY_REQUESTS = 429;

    /**
     * Handle response
     *
     * @param ResponseInterface $response
     *
     * @return void
     * @throws ExportRequestException
     */
    public function handle(ResponseInterface $response): void
    {
        if ($response->getStatusCode() !== self::STATUS_OK) {
            throw new ExportRequestException(
                __($response->getErrorMessage()),
                $this->isNeedUpdateRetryCounter($response)
            );
        }
    }

    /**
     * Checks whether is need to update retry counter
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isNeedUpdateRetryCounter(ResponseInterface $response): bool
    {
        $statusCode = $response->getStatusCode();

        return !($statusCode >= 500 || $statusCode === self::TOO_MANY_REQUESTS);
    }
}
