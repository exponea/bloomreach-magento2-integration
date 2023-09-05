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
 * The class responsible to call API request and start import
 */
class StartApiImportService
{
    /**
     * Endpoint pattern '/imports/v1/{project_token}/{import_id}/run'
     */
    public const URL_ENDPOINT_PATTERN = '%s/imports/v1/%s/%s/run';

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
     * @var array
     */
    private $endpoint = [];

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
     * Start import
     *
     * @param string $importId
     * @param string $csvFilePath
     * @param bool $testConnection
     *
     * @return ResponseInterface
     *
     * @SuppressWarnings(PMD.BooleanArgumentFlag)
     */
    public function execute(string $importId, string $csvFilePath = '', bool $testConnection = false): ResponseInterface
    {
        if (!$csvFilePath) {
            /** @var ResponseInterface $response */
            $response = $this->responseFactory->create();
            $response->setErrorMessage(__('Nothing to send')->render());

            return $response;
        }

        $body = ['test_connection' => $testConnection];

        if ($testConnection === false) {
            $body = ['path_to_overwrite' => $csvFilePath];
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
