<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
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
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @param ConfigProvider $configProvider
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ConfigProvider $configProvider,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->configProvider = $configProvider;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Send Event Request
     *
     * @param array $body
     *
     * @return Response
     */
    public function execute(array $body): Response
    {
        if (!$body) {
            /** @var Response $response */
            return $this->responseFactory->create([
                'reason' => __('Nothing to send')
            ]);
        }

        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $this->getEndpoint(),
            'auth' => $this->getAuthData(),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]]);

        try {
            $response = $client->request(
                static::REQUEST_TYPE,
                $client->getConfig('base_uri'),
                [
                    'json' => $body
                ]
            );
        } catch (GuzzleException $exception) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage()
            ]);
        }

        return $response;
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

    /**
     * Get authorization data
     *
     * @return array
     */
    private function getAuthData(): array
    {
        return [
            $this->configProvider->getApiKeyId(),
            $this->configProvider->getApiSecret()
        ];
    }
}
