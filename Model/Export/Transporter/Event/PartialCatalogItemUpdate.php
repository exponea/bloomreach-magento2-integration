<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Event;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Transporter\ResponseHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\PartialCatalogItemUpdateRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * The class is responsible for partial catalog item update
 */
class PartialCatalogItemUpdate implements TransporterInterface
{
    /**
     * @var PartialCatalogItemUpdateRequest
     */
    private $partialCatalogItemUpdateRequest;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param PartialCatalogItemUpdateRequest $partialCatalogItemUpdateRequest
     * @param SerializerInterface $jsonSerializer
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        PartialCatalogItemUpdateRequest $partialCatalogItemUpdateRequest,
        SerializerInterface $jsonSerializer,
        ResponseHandler $responseHandler
    ) {
        $this->partialCatalogItemUpdateRequest = $partialCatalogItemUpdateRequest;
        $this->jsonSerializer = $jsonSerializer;
        $this->responseHandler = $responseHandler;
    }

    /**
     * Sends data to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        $properties = $this->jsonSerializer->unserialize($exportQueue->getBody());
        $itemId = $properties['item_id'] ?? '';

        //Unset primary id to avoid duplicating the Primary field in the Bloomreach catalog
        if (isset($properties['item_id'])) {
            unset($properties['item_id']);
        }

        $body = ['properties' => $properties];
        $this->responseHandler->handle(
            $this->partialCatalogItemUpdateRequest->execute($body, $itemId, $exportQueue->getEntityType())
        );

        return true;
    }
}
