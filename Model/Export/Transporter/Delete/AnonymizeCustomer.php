<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Delete;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Transporter\ResponseHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\AnonymizeCustomerRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Anonymize customer on the Bloomreach side
 */
class AnonymizeCustomer implements TransporterInterface
{
    /**
     * @var AnonymizeCustomerRequest
     */
    private $anonymizeCustomerRequest;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param AnonymizeCustomerRequest $anonymizeCustomerRequest
     * @param SerializerInterface $jsonSerializer
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        AnonymizeCustomerRequest $anonymizeCustomerRequest,
        SerializerInterface $jsonSerializer,
        ResponseHandler $responseHandler
    ) {
        $this->anonymizeCustomerRequest = $anonymizeCustomerRequest;
        $this->jsonSerializer = $jsonSerializer;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Sends anonymize request to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        $this->responseHandler->handle(
            $this->anonymizeCustomerRequest->execute(
                $this->jsonSerializer->unserialize($exportQueue->getBody())
            )
        );

        return true;
    }
}
