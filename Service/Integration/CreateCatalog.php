<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Integration;

use Bloomreach\EngagementConnector\Exception\AuthenticationException;
use Bloomreach\EngagementConnector\Exception\AuthorizationException;
use Bloomreach\EngagementConnector\Exception\BadRequestException;
use Bloomreach\EngagementConnector\Exception\NotFoundException;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use Bloomreach\EngagementConnector\Service\Integration\Response\ResponseValidator;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Rest\Request;

/**
 * The class is responsible for creating a catalog
 */
class CreateCatalog
{
    /**
     * Endpoint pattern '/data/v2/projects/{projectToken}/catalogs'
     */
    public const URL_ENDPOINT_PATTERN = '%s/data/v2/projects/%s/catalogs';

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
     * @var ResponseValidator
     */
    private $responseValidator;

    /**
     * @param ConfigProvider $configProvider
     * @param RequestSender $requestSender
     * @param ResponseValidator $responseValidator
     */
    public function __construct(
        ConfigProvider $configProvider,
        RequestSender $requestSender,
        ResponseValidator $responseValidator
    ) {
        $this->configProvider = $configProvider;
        $this->requestSender = $requestSender;
        $this->responseValidator = $responseValidator;
    }

    /**
     * Creates catalog
     *
     * @param array $body
     *
     * @return string
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NotFoundException
     * @throws LocalizedException
     */
    public function execute(array $body): string
    {
        $response= $this->requestSender->execute($this->getEndpoint(), self::REQUEST_TYPE, $body);

        try {
            $this->responseValidator->validate($response);
            $catalogId = $response->getData()['id'] ?? '';
        } catch (BadRequestException $e) {
            throw new LocalizedException(
                __(
                    'Failed to create a catalog. Original error message: %error_message.',
                    ['error_message' => $e->getMessage()]
                )
            );
        }

        if (!$catalogId) {
            throw new LocalizedException(
                __(
                    'Failed to create a catalog. There is no catalog ID in the response.',
                )
            );
        }

        return $catalogId;
    }

    /**
     * Returns endpoint url
     *
     * @return string
     */
    private function getEndpoint(): string
    {
        return sprintf(
            static::URL_ENDPOINT_PATTERN,
            $this->configProvider->getApiTarget(),
            $this->configProvider->getProjectTokenId()
        );
    }
}
