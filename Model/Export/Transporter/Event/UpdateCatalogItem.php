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
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\FieldsMapper;
use Bloomreach\EngagementConnector\Service\Integration\UpdateCatalogItemRequest;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Updates Catalog Item on the Bloomreach
 */
class UpdateCatalogItem implements TransporterInterface
{
    /**
     * @var UpdateCatalogItemRequest
     */
    private $updateCatalogItemRequest;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ResponseHandler
     */
    private $responseHandler;

    /**
     * @param UpdateCatalogItemRequest $updateCatalogItemRequest
     * @param SerializerInterface $jsonSerializer
     * @param ResponseHandler $responseHandler
     */
    public function __construct(
        UpdateCatalogItemRequest $updateCatalogItemRequest,
        SerializerInterface $jsonSerializer,
        ResponseHandler $responseHandler
    ) {
        $this->updateCatalogItemRequest = $updateCatalogItemRequest;
        $this->jsonSerializer = $jsonSerializer;
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
        $properties = $this->jsonSerializer->unserialize($exportQueue->getBody());
        $itemId = $properties[FieldsMapper::PRIMARY_ID] ?? '';

        //Unset primary id to avoid duplicating the Primary field in the Bloomreach catalog
        if (isset($properties[FieldsMapper::PRIMARY_ID])) {
            unset($properties[FieldsMapper::PRIMARY_ID]);
        }

        $body = ['properties' => $properties];
        $this->responseHandler->handle(
            $this->updateCatalogItemRequest->execute($body, $itemId, $exportQueue->getEntityType())
        );

        return true;
    }
}
