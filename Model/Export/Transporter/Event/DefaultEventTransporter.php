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
use Bloomreach\EngagementConnector\Service\Integration\SendEventRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Sends event data to the Bloomreach service
 */
class DefaultEventTransporter implements TransporterInterface
{
    /**
     * @var SendEventRequest
     */
    private $sendEventRequest;

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
     * @param SendEventRequest $sendEventRequest
     * @param SerializerInterface $jsonSerializer
     * @param RegisteredGenerator $registeredGenerator
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        SendEventRequest $sendEventRequest,
        SerializerInterface $jsonSerializer,
        RegisteredGenerator $registeredGenerator,
        ResponseHandler $responseHandler
    ) {
        $this->sendEventRequest = $sendEventRequest;
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
        $this->responseHandler->handle($this->sendEventRequest->execute($this->buildEventBody($exportQueue)));

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
            $this->deleteUnusedFields($properties);
        }

        return [
            'customer_ids' => $this->jsonSerializer->unserialize($exportQueue->getRegistered()),
            'properties' => $properties,
            'event_type' => $exportQueue->getEntityType(),
            'timestamp' => strtotime($exportQueue->getCreatedAt())
        ];
    }

    /**
     * Delete unused fields
     *
     * @param array $properties
     *
     * @return void
     */
    private function deleteUnusedFields(array &$properties): void
    {
        $this->registeredGenerator->deleteRegisteredData($properties);

        if (isset($properties['timestamp'])) {
            unset($properties['timestamp']);
        }
    }
}
