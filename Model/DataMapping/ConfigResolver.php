<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\Data\FieldMappingConfigData;
use Bloomreach\EngagementConnector\Model\DataMapping\Config\Data\FieldMappingConfigDataInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\Config\Data\FieldMappingConfigDataInterfaceFactory;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Allows retrieve mapping config for specific entity type
 */
class ConfigResolver
{
    /**
     * @var DataInterface
     */
    private $dataStorage;

    /**
     * @var array
     */
    private $configCache = [];

    /**
     * @var FieldMappingConfigDataInterfaceFactory
     */
    private $fieldMappingConfigDataInterfaceFactory;

    /**
     * @param FieldMappingConfigDataInterfaceFactory $fieldMappingConfigDataInterfaceFactory
     * @param DataInterface $dataStorage
     */
    public function __construct(
        FieldMappingConfigDataInterfaceFactory $fieldMappingConfigDataInterfaceFactory,
        DataInterface $dataStorage
    ) {
        $this->dataStorage = $dataStorage;
        $this->fieldMappingConfigDataInterfaceFactory = $fieldMappingConfigDataInterfaceFactory;
    }

    /**
     * Create mapping config for specific entity type
     *
     * @param string $entityType
     *
     * @return FieldMappingConfigDataInterface[]
     * @throws NotFoundException
     */
    public function getByEntityType(string $entityType): array
    {
        if (!array_key_exists($entityType, $this->configCache)) {
            $this->configCache[$entityType] = $this->createByEntityType($entityType);
        }

        return $this->configCache[$entityType];
    }

    /**
     * Create config data for entity type
     *
     * @param string $entityType
     *
     * @return FieldMappingConfigDataInterface[]
     * @throws NotFoundException
     */
    private function createByEntityType(string $entityType): array
    {
        $configData = $this->dataStorage->get($entityType);

        if (!$configData) {
            throw new NotFoundException(
                __(
                    'There are not registered mapping config for "%1" entity type',
                    $entityType
                )
            );
        }

        return $this->prepareConfigData($configData);
    }

    /**
     * Prepare config data
     *
     * @param array $configData
     *
     * @return FieldMappingConfigDataInterface[]
     */
    private function prepareConfigData(array $configData): array
    {
        $result = [];

        foreach ($configData as $configField) {
            $bloomreachCode = $configField['bloomreach_code'] ?? '';
            $field = $configField['field'] ?? '';

            if (!$bloomreachCode || !$field) {
                continue;
            }

            $result[] = $this->fieldMappingConfigDataInterfaceFactory->create(
                [
                    'data' => [
                        FieldMappingConfigData::BLOOMREACH_CODE => $bloomreachCode,
                        FieldMappingConfigData::FIELD => $field
                    ]
                ]
            );
        }

        return $result;
    }
}
