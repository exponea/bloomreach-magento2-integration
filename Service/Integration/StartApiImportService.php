<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\ConfigProvider;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;

/**
 * The class responsible to call API request and start import
 */
class StartApiImportService
{
    /**
     * Endpoint pattern '/data/v2/projects/{projectToken}/imports/{import_id}/start'
     */
    public const URL_ENDPOINT_PATTERN = '%s/data/v2/projects/%s/imports/%s/start';

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
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var array
     */
    private $endpoint = [];

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
    public function execute(string $importId, string $csvFilePath = '', bool $testConnection = false): Response
    {
        if (!$csvFilePath) {
            /** @var Response $response */
            return $this->responseFactory->create(
                [
                    'reason' => __('The path of CSV file is not exist')
                ]
            );
        }

        $body = ['test_connection' => $testConnection];

        if ($testConnection === false) {
            $body = ['path' => $csvFilePath];
        }

        return $this->requestSender->execute($this->getEndpoint($importId), static::REQUEST_TYPE, $body);
    }

    /**
     * Returns event endpoint
     *
     * @param string $importId
     *
     * @return string
     */
    private function getEndpoint(string $importId): string
    {
        if (!array_key_exists($importId, $this->endpoint)) {
            $apiBaseUrl = $this->configProvider->getApiTarget();
            $projectToken = $this->configProvider->getProjectTokenId();

            $this->endpoint[$importId] = sprintf(
                self::URL_ENDPOINT_PATTERN,
                $apiBaseUrl,
                $projectToken,
                $importId
            );
        }

        return $this->endpoint[$importId];
    }
}
