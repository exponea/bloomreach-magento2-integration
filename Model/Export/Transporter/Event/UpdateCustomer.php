<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Event;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Export\Transporter\ResponseHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\UpdateCustomerRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Updates customer on the Bloomreach
 */
class UpdateCustomer implements TransporterInterface
{
    /**
     * @var UpdateCustomerRequest
     */
    private $updateCustomerRequest;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var RegisteredGenerator
     */
    private $registeredGenerator;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param UpdateCustomerRequest $updateCustomerRequest
     * @param SerializerInterface $jsonSerializer
     * @param RegisteredGenerator $registeredGenerator
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        UpdateCustomerRequest $updateCustomerRequest,
        SerializerInterface $jsonSerializer,
        RegisteredGenerator $registeredGenerator,
        ResponseHandler $responseHandler
    ) {
        $this->updateCustomerRequest = $updateCustomerRequest;
        $this->jsonSerializer = $jsonSerializer;
        $this->registeredGenerator = $registeredGenerator;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Sends event data to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        $this->responseHandler->handle($this->updateCustomerRequest->execute($this->buildEventBody($exportQueue)));

        return true;
    }

    /**
     * Build event body
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return array
     */
    private function buildEventBody(ExportQueueInterface $exportQueue): array
    {
        $properties = $this->jsonSerializer->unserialize($exportQueue->getBody());

        if (is_array($properties)) {
            $this->registeredGenerator->deleteRegisteredData($properties);
        }

        return [
            'customer_ids' => $this->jsonSerializer->unserialize($exportQueue->getRegistered()),
            'properties' => $properties,
            'update_timestamp' => strtotime($exportQueue->getCreatedAt())
        ];
    }
}
