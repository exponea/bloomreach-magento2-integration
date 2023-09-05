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
use Bloomreach\EngagementConnector\System\CatalogIdResolver;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
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
     * @var ResponseInterfaceFactory
     */
    private $responseFactory;

    /**
     * @var CatalogIdResolver
     */
    private $catalogIdResolver;

    /**
     * @param ConfigProvider $configProvider
     * @param RequestSender $requestSender
     * @param ResponseInterfaceFactory $responseFactory
     * @param CatalogIdResolver $catalogIdResolver
     */
    public function __construct(
        ConfigProvider $configProvider,
        RequestSender $requestSender,
        ResponseInterfaceFactory $responseFactory,
        CatalogIdResolver $catalogIdResolver
    ) {
        $this->configProvider = $configProvider;
        $this->requestSender = $requestSender;
        $this->responseFactory = $responseFactory;
        $this->catalogIdResolver = $catalogIdResolver;
    }

    /**
     * Send Event Request
     *
     * @param array $body
     * @param string $itemId
     * @param string $entityType
     *
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function execute(array $body, string $itemId, string $entityType): ResponseInterface
    {
        if (!$body && static::REQUEST_TYPE !== Request::HTTP_METHOD_DELETE) {
            /** @var ResponseInterface $response */
            $response = $this->responseFactory->create();
            $response->setErrorMessage(__('Nothing to send')->render());

            return $response;
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
     * @throws LocalizedException
     */
    private function getEndpoint(string $itemId, string $entityType): string
    {
        $apiBaseUrl = $this->configProvider->getApiTarget();
        $projectToken = $this->configProvider->getProjectTokenId();

        return sprintf(
            static::URL_ENDPOINT_PATTERN,
            $apiBaseUrl,
            $projectToken,
            $this->catalogIdResolver->getCatalogId($entityType),
            $itemId
        );
    }
}
