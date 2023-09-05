<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration\Response;

use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;
use Bloomreach\EngagementConnector\Api\Data\ResponseInterfaceFactory;
use Bloomreach\EngagementConnector\Data\ResponseData;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for converting the GuzzleHttpResponse response into ResponseInterface
 */
class ResponseConverter
{
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ResponseInterfaceFactory
     */
    private $responseDataFactory;

    /**
     * @param SerializerInterface $jsonSerializer
     * @param ResponseInterfaceFactory $responseDataFactory
     */
    public function __construct(
        SerializerInterface $jsonSerializer,
        ResponseInterfaceFactory $responseDataFactory
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->responseDataFactory = $responseDataFactory;
    }

    /**
     * Convert Api response
     *
     * @param Response $response
     *
     * @return ResponseInterface
     */
    public function execute(Response $response): ResponseInterface
    {
        /** @var ResponseInterface $responseData */
        $responseData = $this->responseDataFactory->create();
        $responseData->setStatusCode($response->getStatusCode());
        $responseData->setData($this->getBody($response));

        if ($responseData->getStatusCode() !== ResponseValidator::STATUS_OK) {
            $responseData->setErrorMessage(
                sprintf(
                    'Error: %s, %s',
                    $response->getReasonPhrase(),
                    $this->getErrorMessage($responseData)
                )
            );
        }

        return $responseData;
    }

    /**
     * Get Error message from response
     *
     * @param ResponseData $responseData
     *
     * @return string
     */
    private function getErrorMessage(ResponseData $responseData): string
    {
        $error = $responseData->getData()['error'] ?? '';

        if ($error) {
            return $error;
        }

        $responseErrors = $responseData->getData()['errors'] ?? [];
        $errors = [];

        foreach ($responseErrors as $error) {
            if (is_array($error)) {
                foreach ($error as $item) {
                    if (is_string($item)) {
                        $errors[] = $item;
                    }
                }
            } elseif (is_string($error)) {
                $errors[] = $error;
            }
        }

        if ($errors) {
            return implode('. ', $errors);
        }

        return $item['message'] ?? '';
    }

    /**
     * Get Response body
     *
     * @param Response $response
     *
     * @return array
     */
    private function getBody(Response $response): array
    {
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents();

        try {
            $body = $this->jsonSerializer->unserialize($responseContent);
        } catch (InvalidArgumentException $e) {
            $body = [];
        }

        return $body;
    }
}
