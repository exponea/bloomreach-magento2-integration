<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\Data\FieldMappingConfigDataInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;

/**
 * Maps Entity data to Bloomreach data
 */
class MappingProcessor
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var FieldValueRendererResolver
     */
    private $fieldValueRendererResolver;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param FieldValueRendererResolver $fieldValueRendererResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        FieldValueRendererResolver $fieldValueRendererResolver,
        LoggerInterface $logger
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->fieldValueRendererResolver = $fieldValueRendererResolver;
        $this->logger = $logger;
    }

    /**
     * Maps entity data to Bloomreach data
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $entityType
     * @param FieldMappingConfigDataInterface[] $mapConfig
     *
     * @return DataObject
     */
    public function map($entity, string $entityType, array $mapConfig): DataObject
    {
        $data = [];

        foreach ($mapConfig as $fieldMappingConfigData) {
            try {
                $data[$fieldMappingConfigData->getBloomreachCode()] =
                    $this->fieldValueRendererResolver->render(
                        $entityType,
                        $fieldMappingConfigData->getField(),
                        $entity
                    );
            } catch (\Exception $e) {
                $this->logger->error(
                    __(
                        'An error occurred while mapping %1 field data for %2 entity type. Error: %3',
                        $fieldMappingConfigData->getBloomreachCode(),
                        $entityType,
                        $e->getMessage()
                    )
                );
            }
        }

        return $this->dataObjectFactory->create(['data' => $data]);
    }
}
