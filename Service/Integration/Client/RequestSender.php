<?php

declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration\Client;

use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;
use Bloomreach\EngagementConnector\Logger\Debugger;
use Bloomreach\EngagementConnector\Service\Integration\Response\ResponseConverter;
use Bloomreach\EngagementConnector\Service\Integration\Validator\CredentialValidator;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use GuzzleHttp\RequestOptions;
use Magento\Framework\Validation\ValidationException;

/**
 * The class is responsible for calling API request
 */
class RequestSender
{
    private const MAP_ERROR_MESSAGE_TO_CODE = [
        'curl error 28' => 504,
        'curl error 5' => 503,
        'curl error 6' => 401,
        'curl error 7' => 502
    ];

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
     * @var Debugger
     */
    private $debugger;

    /**
     * @var CredentialValidator
     */
    private $credentialValidator;

    /**
     * @var ResponseConverter
     */
    private $responseConverter;

    /**
     * @param ConfigProvider $configProvider
     * @param ClientFactory $clientFactory
     * @param ResponseFactory $responseFactory
     * @param Debugger $debugger
     * @param CredentialValidator $credentialValidator
     * @param ResponseConverter $responseConverter
     */
    public function __construct(
        ConfigProvider $configProvider,
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        Debugger $debugger,
        CredentialValidator $credentialValidator,
        ResponseConverter $responseConverter
    ) {
        $this->configProvider = $configProvider;
        $this->clientFactory = $clientFactory;
        $this->responseFactory = $responseFactory;
        $this->debugger = $debugger;
        $this->credentialValidator = $credentialValidator;
        $this->responseConverter = $responseConverter;
    }

    /**
     * Send api request
     *
     * @param string $endpoint
     * @param string $requestType
     * @param array $body
     *
     * @return ResponseInterface
     * @throws ValidationException
     */
    public function execute(string $endpoint, string $requestType, array $body): ResponseInterface
    {
        $validationResult = $this->credentialValidator->execute();
        if (!$validationResult->isValid()) {
            throw new ValidationException(
                __('Invalid Credentials.'),
                null,
                0,
                $validationResult
            );
        }

        $this->debugger->log(__('Request Url: %1', $endpoint));
        $this->debugger->log(__('Request Method Type: %1', $requestType));
        $this->debugger->logArray('Request Payload: %1', $body);

        /** @var Client $client */
        $client = $this->clientFactory->create(
            [
                'config' => [
                    'base_uri' => $endpoint,
                    'auth' => $this->getAuthData(),
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ]
                ]
            ]
        );

        try {
            $response = $client->request(
                $requestType,
                $client->getConfig('base_uri'),
                $this->getRequestOptions($body)
            );
        } catch (ConnectException $e) {
            $statusCode = $e->getCode() ?: $this->getErrorCode($e->getMessage());
            $response = $this->getResponse($statusCode, $e->getMessage());
        } catch (GuzzleException $e) {
            $response = method_exists($e, 'getResponse')
                ? $e->getResponse() : $this->getResponse((int) $e->getCode(), $e->getMessage());
        }

        $responseData = $this->responseConverter->execute($response);
        $this->debugger->log(__('Response Status: %1', $responseData->getStatusCode()));
        $this->debugger->log(__('Response Data: %1', $response->getReasonPhrase()));
        $this->debugger->logArray('Response Body: %1', $responseData->getData());

        return $responseData;
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

    /**
     * Returns request options
     *
     * @param array $body
     *
     * @return array
     */
    private function getRequestOptions(array $body): array
    {
        $options[RequestOptions::TIMEOUT] = $this->configProvider->getRequestTimeout();

        if ($body) {
            $options['json'] = $body;
        }

        return $options;
    }

    /**
     * Create response
     *
     * @param int $statusCode
     * @param string $reason
     *
     * @return Response
     */
    private function getResponse(int $statusCode, string $reason): Response
    {
        return $this->responseFactory->create(
            [
                'status' => $statusCode,
                'reason' => $reason
            ]
        );
    }

    /**
     * Get error code
     *
     * @param string $errorMessage
     *
     * @return int
     */
    private function getErrorCode(string $errorMessage): int
    {
        $errorMessage = strtolower($errorMessage);

        foreach (self::MAP_ERROR_MESSAGE_TO_CODE as $message => $statusCode) {
            if (preg_match('#' . $message  . '#', $errorMessage)) {
                return $statusCode;
            }
        }

        return 500;
    }
}
