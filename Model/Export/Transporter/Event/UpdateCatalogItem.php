<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Transporter\Event;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\ConfigResolver;
use Bloomreach\EngagementConnector\Model\Export\Transporter\ResponseHandler;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterInterface;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog\FieldsMapper;
use Bloomreach\EngagementConnector\Service\BooleanConverter;
use Bloomreach\EngagementConnector\Service\Integration\UpdateCatalogItemRequest;
use Bloomreach\EngagementConnector\Service\ValueTypeGetter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
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
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var BooleanConverter
     */
    private $booleanConverter;

    /**
     * @param UpdateCatalogItemRequest $updateCatalogItemRequest
     * @param SerializerInterface $jsonSerializer
     * @param ResponseHandler $responseHandler
     * @param ConfigResolver $configResolver
     * @param BooleanConverter $booleanConverter
     */
    public function __construct(
        UpdateCatalogItemRequest $updateCatalogItemRequest,
        SerializerInterface $jsonSerializer,
        ResponseHandler $responseHandler,
        ConfigResolver $configResolver,
        BooleanConverter $booleanConverter
    ) {
        $this->updateCatalogItemRequest = $updateCatalogItemRequest;
        $this->jsonSerializer = $jsonSerializer;
        $this->responseHandler = $responseHandler;
        $this->configResolver = $configResolver;
        $this->booleanConverter = $booleanConverter;
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
        $properties = $this->updateBooleanProperties(
            $this->jsonSerializer->unserialize($exportQueue->getBody()),
            $exportQueue->getEntityType()
        );
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

    /**
     * Updates boolean properties in the given array based on the entity type configuration.
     *
     * @param array $properties
     * @param string $entityType
     *
     * @return array
     * @throws NotFoundException
     */
    private function updateBooleanProperties(array $properties, string $entityType): array
    {
        $fields = $this->configResolver->getByEntityType($entityType);

        foreach ($fields as $field) {
            if ($field->getType() !== ValueTypeGetter::BOOLEAN_TYPE) {
                continue;
            }

            if (!isset($properties[$field->getBloomreachCode()])) {
                continue;
            }

            $properties[$field->getBloomreachCode()] =
                $this->booleanConverter->toBool($properties[$field->getBloomreachCode()]);
        }

        return $properties;
    }
}
