<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Event;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\PartialCatalogItemUpdateRequest;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * @param PartialCatalogItemUpdateRequest $partialCatalogItemUpdateRequest
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        PartialCatalogItemUpdateRequest $partialCatalogItemUpdateRequest,
        SerializerInterface $jsonSerializer
    ) {
        $this->partialCatalogItemUpdateRequest = $partialCatalogItemUpdateRequest;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Sends data to the Bloomreach service
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
        $properties = $this->jsonSerializer->unserialize($exportQueue->getBody());
        $itemId = $properties['item_id'] ?? '';
        $body = ['properties' => $properties];

        $response = $this->partialCatalogItemUpdateRequest->execute($body, $itemId, $exportQueue->getEntityType());

        if ((int) $response->getStatusCode() !== 200) {
            throw new LocalizedException(__($response->getReasonPhrase()));
        }

        return true;
    }
}
