<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use GuzzleHttp\Psr7\Response;
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
     * @param ConfigProvider $configProvider
     * @param RequestSender $requestSender
     */
    public function __construct(
        ConfigProvider $configProvider,
        RequestSender $requestSender
    ) {
        $this->configProvider = $configProvider;
        $this->requestSender = $requestSender;
    }

    /**
     * Get current time of Bloomreach platform
     *
     * @return Response
     */
    public function execute(): Response
    {
        return $this->requestSender->execute($this->getEndpoint(), self::REQUEST_TYPE, []);
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
