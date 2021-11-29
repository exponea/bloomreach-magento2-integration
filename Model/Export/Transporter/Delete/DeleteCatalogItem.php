<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Delete;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Service\Integration\DeleteCatalogItemRequest;
use Magento\Framework\Exception\LocalizedException;

/**
 * Delete catalog item on the Bloomreach side
 */
class DeleteCatalogItem implements TransporterInterface
{
    /**
     * @var DeleteCatalogItemRequest
     */
    private $deleteCatalogItemRequest;

    /**
     * @param DeleteCatalogItemRequest $deleteCatalogItemRequest
     */
    public function __construct(DeleteCatalogItemRequest $deleteCatalogItemRequest)
    {
        $this->deleteCatalogItemRequest = $deleteCatalogItemRequest;
    }

    /**
     * Sends delete request to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool
    {
        $response = $this->deleteCatalogItemRequest->execute(
            [],
            $exportQueue->getBody(),
            $exportQueue->getEntityType()
        );

        if ((int) $response->getStatusCode() !== 200) {
            throw new LocalizedException(__($response->getReasonPhrase()));
        }

        return true;
    }
}
