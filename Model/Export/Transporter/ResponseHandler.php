<?php

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter;

use Bloomreach\EngagementConnector\Exception\ExportRequestException;
use GuzzleHttp\Psr7\Response;

/**
 * The class is responsible for handling export response
 */
class ResponseHandler
{
    private const TOO_MANY_REQUESTS = 429;

    /**
     * Handle response
     *
     * @param Response $response
     *
     * @return void
     * @throws ExportRequestException
     */
    public function handle(Response $response): void
    {
        if ($response->getStatusCode() !== 200) {
            throw new ExportRequestException(
                __($response->getReasonPhrase()),
                $this->isNeedUpdateRetryCounter($response)
            );
        }
    }

    /**
     * Checks whether is need to update retry counter
     *
     * @param Response $response
     *
     * @return bool
     */
    private function isNeedUpdateRetryCounter(Response $response): bool
    {
        $statusCode = $response->getStatusCode();

        return !($statusCode >= 500 || $statusCode === self::TOO_MANY_REQUESTS);
    }
}
