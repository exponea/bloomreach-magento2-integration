<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Api request for update catalog
 */
class UpdateCatalogItemRequest
{
    /**
     * Endpoint pattern '/data/v2/projects/{projectToken}/catalogs/{catalogId}/items/{itemId}'
     */
    public const URL_ENDPOINT_PATTERN = '%s/data/v2/projects/%s/catalogs/%s/items/%s';

    public const REQUEST_TYPE = Request::HTTP_METHOD_PUT;

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
     * @param string $itemId
     * @param string $entityType
     *
     * @return Response
     */
    public function execute(array $body, string $itemId, string $entityType): Response
    {
        if (!$body && static::REQUEST_TYPE !== Request::HTTP_METHOD_DELETE) {
            /** @var Response $response */
            return $this->responseFactory->create([
                'reason' => __('Nothing to send')
            ]);
        }

        /** @var Client $client */
        $client = $this->clientFactory->create(['config' => [
            'base_uri' => $this->getEndpoint($itemId, $entityType),
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
                $body ? ['json' => $body] : []
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
     * @param string $itemId
     * @param string $entityType
     *
     * @return string
     */
    private function getEndpoint(string $itemId, string $entityType): string
    {
        $apiBaseUrl = $this->configProvider->getApiTarget();
        $projectToken = $this->configProvider->getProjectTokenId();

        switch ($entityType) {
            case ProductVariantsType::ENTITY_TYPE:
                $catalogId = $this->configProvider->getCatalogVariantsId();
                break;
            default:
                $catalogId = $this->configProvider->getCatalogId();
        }

        return sprintf(
            static::URL_ENDPOINT_PATTERN,
            $apiBaseUrl,
            $projectToken,
            $catalogId,
            $itemId
        );
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
