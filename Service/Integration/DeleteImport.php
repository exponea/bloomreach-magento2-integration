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
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;

/**
 * The class is responsible for deleting import
 */
class DeleteImport
{
    /**
     * Endpoint pattern '/imports/v1/{projectToken}/{{import_id}}'
     */
    public const URL_ENDPOINT_PATTERN = '%s/imports/v1/%s/%s';

    public const REQUEST_TYPE = Request::HTTP_METHOD_DELETE;

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
     * Get Import
     *
     * @param string $importId
     *
     * @return bool
     * @throws AuthenticationException
     * @throws AuthorizationException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(string $importId): bool
    {
        $response = $this->requestSender->execute($this->getEndpoint($importId), self::REQUEST_TYPE, []);

        try {
            $this->responseValidator->validate($response);
        } catch (NotFoundException $e) {
            throw new NoSuchEntityException(
                __(
                    'There is no such import definition with ID: %import_id',
                    ['import_id' => $importId]
                )
            );
        } catch (BadRequestException $e) {
            throw new LocalizedException(
                __(
                    'Failed to delete an import. Original error message: %error_message.',
                    ['error_message' => $e->getMessage()]
                )
            );
        }

        return true;
    }

    /**
     * Returns endpoint url
     *
     * @param string $importId
     *
     * @return string
     */
    private function getEndpoint(string $importId): string
    {
        return sprintf(
            static::URL_ENDPOINT_PATTERN,
            $this->configProvider->getApiTarget(),
            $this->configProvider->getProjectTokenId(),
            $importId
        );
    }
}
