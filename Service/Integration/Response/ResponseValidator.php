<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration\Response;

use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;
use Bloomreach\EngagementConnector\Exception\AuthenticationException;
use Bloomreach\EngagementConnector\Exception\AuthorizationException;
use Bloomreach\EngagementConnector\Exception\BadRequestException;
use Bloomreach\EngagementConnector\Exception\NotFoundException;

/**
 * The class is responsible for validating a response
 */
class ResponseValidator
{
    public const STATUS_OK = 200;

    public const NOT_FOUND = 404;

    public const AUTH_ERROR = 401;

    public const ACCESS_ERROR = 403;

    public const BAD_REQUEST_ERROR = 400;

    public const ADDITIONAL_AUTH_ERRORS = [
        'Could not authenticate' => self::BAD_REQUEST_ERROR
    ];

    /**
     * Validates response
     *
     * @param ResponseInterface $response
     *
     * @return void
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function validate(ResponseInterface $response): void
    {
        if ($response->getStatusCode() === self::STATUS_OK) {
            return;
        }

        if ($this->isAuthError($response)) {
            throw new AuthenticationException(__($response->getErrorMessage()));
        }

        if ($response->getStatusCode() === self::ACCESS_ERROR) {
            throw new AuthorizationException(__($response->getErrorMessage()));
        }

        if ($response->getStatusCode() === self::NOT_FOUND) {
            throw new NotFoundException(__($response->getErrorMessage()));
        }

        if ($response->getStatusCode() !== self::STATUS_OK) {
            throw new BadRequestException(__($response->getErrorMessage()));
        }
    }

    /**
     * Checks if there is an authorization error
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isAuthError(ResponseInterface $response): bool
    {
        if ($response->getStatusCode() === self::AUTH_ERROR) {
            return true;
        }

        foreach (self::ADDITIONAL_AUTH_ERRORS as $errorMessage => $statusCode) {
            if ($response->getStatusCode() === $statusCode
                && preg_match('#' . $errorMessage . '#', $response->getErrorMessage())
            ) {
                return true;
            }
        }

        return false;
    }
}
