<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Event;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\Event\RegisteredGenerator;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\SendEventRequest;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @param SendEventRequest $sendEventRequest
     * @param SerializerInterface $jsonSerializer
     * @param RegisteredGenerator $registeredGenerator
     */
    public function __construct(
        SendEventRequest $sendEventRequest,
        SerializerInterface $jsonSerializer,
        RegisteredGenerator $registeredGenerator
    ) {
        $this->sendEventRequest = $sendEventRequest;
        $this->jsonSerializer = $jsonSerializer;
        $this->registeredGenerator = $registeredGenerator;
    }

    /**
     * Sends event data to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     *
     * @throws FileSystemException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        $response = $this->sendEventRequest->execute($this->buildEventBody($exportQueue));

        if ((int) $response->getStatusCode() !== 200) {
            throw new LocalizedException(__($response->getReasonPhrase()));
        }

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
