<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use GuzzleHttp\ClientFactory;
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
     * @var RequestSender
     */
    private $requestSender;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @param ConfigProvider $configProvider
     * @param RequestSender $requestSender
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        ConfigProvider $configProvider,
        RequestSender $requestSender,
        ResponseFactory $responseFactory
    ) {
        $this->configProvider = $configProvider;
        $this->requestSender = $requestSender;
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
            return $this->responseFactory->create(
                [
                    'reason' => __('Nothing to send')
                ]
            );
        }

        return $this->requestSender->execute(
            $this->getEndpoint($itemId, $entityType),
            static::REQUEST_TYPE,
            $body
        );
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
}
