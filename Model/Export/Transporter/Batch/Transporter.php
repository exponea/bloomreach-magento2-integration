<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Batch;

use Bloomreach\EngagementConnector\Api\Data\ResponseInterface;
use Bloomreach\EngagementConnector\Exception\ExportRequestException;
use Bloomreach\EngagementConnector\Model\Export\Transporter\ResponseHandler;
use Bloomreach\EngagementConnector\Service\Integration\BatchCommandsRequest;

/**
 * The class is responsible for sending data to the Bloomreach via batch API endpoint
 */
class Transporter
{
    /**
     * @var BatchCommandsRequest
     */
    private $batchCommandsRequest;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @param BatchCommandsRequest $batchCommandsRequest
     * @param RequestBuilder $requestBuilder
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        BatchCommandsRequest $batchCommandsRequest,
        RequestBuilder $requestBuilder,
        ResponseHandler $responseHandler
    ) {
        $this->batchCommandsRequest = $batchCommandsRequest;
        $this->requestBuilder = $requestBuilder;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Sends data to the Bloomreach service
     *
     * @param array $exportQueueList
     *
     * @return ResponseInterface
     * @throws ExportRequestException
     */
    public function send(array $exportQueueList): ResponseInterface
    {
        $response = $this->batchCommandsRequest->execute($this->requestBuilder->build($exportQueueList));
        $this->responseHandler->handle($response);

        return $response;
    }
}
