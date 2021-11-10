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
 * The class responsible to call API request and start import
 */
class StartApiImportService
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetEndpointService
     */
    private $getEndpointService;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param ConfigProvider $configProvider
     * @param GetEndpointService $getEndpointService
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ConfigProvider $configProvider,
        GetEndpointService $getEndpointService,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory
    ) {
        $this->configProvider = $configProvider;
        $this->getEndpointService = $getEndpointService;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Start import
     *
     * @param string $importId
     * @param string $csvFilePath
     * @param bool $testConnection
     *
     * @return Response
     *
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function execute($importId, $csvFilePath = '', $testConnection = false): Response
    {
        $apiEndpoint = $this->getEndpointService->execute($importId);

        return $this->doRequest($apiEndpoint, $csvFilePath, $testConnection);
    }

    /**
     * Do API request
     *
     * @param string $uriEndpoint
     * @param string $csvFilePath
     * @param bool $testConnection
     *
     * @return Response
     */
    private function doRequest($uriEndpoint, $csvFilePath, $testConnection): Response
    {
        if (!$csvFilePath) {
            /** @var Response $response */
            $response = $this->responseFactory->create([
                'reason' => __('The path of CSV file is not exist')
            ]);

            return $response;
        }

        $requestMethod = Request::HTTP_METHOD_POST;

        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => (string) $uriEndpoint,
            'auth' => $this->getAuthData(),
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ]
        ]]);

        $body = ['test_connection' => (bool) $testConnection];
        if ((bool) $testConnection === false) {
            $body = [
                'path' => $csvFilePath
            ];
        }

        try {
            $response = $client->request(
                $requestMethod,
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
     * Get authorization data
     *
     * @return array
     */
    private function getAuthData()
    {
        $username = $this->configProvider->getApiKeyId();
        $password = $this->configProvider->getApiSecret();

        return [$username, $password];
    }
}
