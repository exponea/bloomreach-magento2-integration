<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\DataMapperInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Model\AbstractModel;

/**
 * Map Magento Entity data to Bloomreach data
 */
class DataMapperResolver
{
    /**
     * @var DataMapperInterface
     */
    private $dataMapperEntity;

    /**
     * @var DataMapperFactory
     */
    private $dataMapperFactory;

    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @param DataMapperFactory $dataMapperFactory
     * @param ConfigResolver $configResolver
     */
    public function __construct(
        DataMapperFactory $dataMapperFactory,
        ConfigResolver $configResolver
    ) {
        $this->dataMapperFactory = $dataMapperFactory;
        $this->configResolver = $configResolver;
    }

    /**
     * Map Magento Entity data to Bloomreach data
     *
     * @param AbstractSimpleObject|AbstractModel $entity
     * @param string $entityType
     *
     * @throws NotFoundException
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     */
    public function map($entity, string $entityType): DataObject
    {
        return $this->getDataMapper($entityType)->map($entity, $this->configResolver->getByEntityType($entityType));
    }

    /**
     * Get instance of data mapper for specified entity type
     *
     * @param string $entityType
     * @return DataMapperInterface
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     */
    private function getDataMapper(string $entityType): DataMapperInterface
    {
        if (!isset($this->dataMapperEntity[$entityType])) {
            $this->dataMapperEntity[$entityType] = $this->dataMapperFactory->create($entityType);
        }

        return $this->dataMapperEntity[$entityType];
    }
}
