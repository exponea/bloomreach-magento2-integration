<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Exception\AuthenticationException;
use Bloomreach\EngagementConnector\Exception\AuthorizationException;
use Bloomreach\EngagementConnector\Exception\BadRequestException;
use Bloomreach\EngagementConnector\Exception\NotFoundException;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use Bloomreach\EngagementConnector\Service\Integration\Response\ResponseValidator;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Webapi\Rest\Request;

/**
 * The class is responsible for getting the current time of the Bloomreach platform in Unix time in seconds.
 */
class GetSystemTimeOfPlatform
{
    /**
     * Endpoint pattern '/track/v2/projects/{projectToken}/system/time'
     */
    public const URL_ENDPOINT_PATTERN = '%s/track/v2/projects/%s/system/time';

    /**
     * Request type
     */
    public const REQUEST_TYPE = Request::HTTP_METHOD_GET;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestSender
     */
    private $requestSender;

    /**
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @param ConfigProvider $configProvider
     * @param RequestSender $requestSender
     * @param ResponseValidator $responseValidator
     */
    public function __construct(
        ConfigProvider $configProvider,
        RequestSender $requestSender,
        ResponseValidator $responseValidator
    ) {
        $this->configProvider = $configProvider;
        $this->requestSender = $requestSender;
        $this->responseValidator = $responseValidator;
    }

    /**
     * Get current time of Bloomreach platform
     *
     * @return float
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws ValidationException
     */
    public function execute(): float
    {
        $response = $this->requestSender->execute($this->getEndpoint(), self::REQUEST_TYPE, []);
        $this->responseValidator->validate($response);

        return (float) ($response->getData()['time'] ?? 0);
    }

    /**
     * Returns endpoint url
     *
     * @return string
     */
    private function getEndpoint(): string
    {
        return sprintf(
            static::URL_ENDPOINT_PATTERN,
            $this->configProvider->getApiTarget(),
            $this->configProvider->getProjectTokenId()
        );
    }
}
