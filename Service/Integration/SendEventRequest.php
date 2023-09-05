<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;
use Bloomreach\EngagementConnector\Api\Data\ResponseInterfaceFactory;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Webapi\Rest\Request;

/**
 * The class is responsible for send event to the Bloomreach API
 */
class SendEventRequest
{
    /**
     * Endpoint pattern '/track/v2/projects/{projectToken}/customers/events'
     */
    public const URL_ENDPOINT_PATTERN = '%s/track/v2/projects/%s/customers/events';

    public const REQUEST_TYPE = Request::HTTP_METHOD_POST;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var RequestSender
     */
    private $requestSender;

    /**
     * @var ResponseInterfaceFactory
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param ConfigProvider $configProvider
     * @param RequestSender $requestSender
     * @param ResponseInterfaceFactory $responseFactory
     */
    public function __construct(
        ConfigProvider $configProvider,
        RequestSender $requestSender,
        ResponseInterfaceFactory $responseFactory
    ) {
        $this->configProvider = $configProvider;
        $this->requestSender = $requestSender;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Send Event Request
     *
     * @param array $body
     *
     * @return ResponseInterface
     */
    public function execute(array $body): ResponseInterface
    {
        if (!$body) {
            /** @var ResponseInterface $response */
            $response = $this->responseFactory->create();
            $response->setErrorMessage(__('Nothing to send')->render());

            return $response;
        }

        return $this->requestSender->execute($this->getEndpoint(), static::REQUEST_TYPE, $body);
    }

    /**
     * Returns event endpoint
     *
     * @return string
     */
    private function getEndpoint(): string
    {
        if ($this->endpoint === null) {
            $apiBaseUrl = $this->configProvider->getApiTarget();
            $projectToken = $this->configProvider->getProjectTokenId();

            $this->endpoint = sprintf(static::URL_ENDPOINT_PATTERN, $apiBaseUrl, $projectToken);
        }

        return $this->endpoint;
    }
}
