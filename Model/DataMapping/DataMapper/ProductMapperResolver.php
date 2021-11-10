<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping\DataMapper;

use Bloomreach\EngagementConnector\Model\DataMapping\Config\Data\FieldMappingConfigDataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;

/**
 * Maps Product data to Bloomreach data
 */
class ProductMapperResolver implements DataMapperInterface
{
    private const DEFAULT_PRODUCT_TYPE = 'default';

    /**
     * @var string[]
     */
    private $dataMappers;

    /**
     * @param array $dataMappers
     */
    public function __construct(array $dataMappers = [])
    {
        $this->dataMappers = $dataMappers;
    }

    /**
     * Maps Product data to Bloomreach data
     *
     * @param AbstractModel $entity
     * @param FieldMappingConfigDataInterface[] $mapConfig
     *
     * @return DataObject
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    public function map($entity, array $mapConfig): DataObject
    {
        return $this->getDataMapper($entity)->map($entity, $mapConfig);
    }

    /**
     * Get instance of data mapper for specified product type
     *
     * @param AbstractModel $product
     *
     * @return DataMapperInterface
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     */
    private function getDataMapper($product): DataMapperInterface
    {
        if (!isset($this->dataMappers[$product->getTypeId()]) &&
            !isset($this->dataMappers[self::DEFAULT_PRODUCT_TYPE])) {
            throw new NoSuchEntityException(
                __(
                    'There is no such data mapper "%1" for interface %2',
                    DataMapperInterface::class
                )
            );
        }

        $dataMapper = $this->dataMappers[$product->getTypeId()] ?? $this->dataMappers[self::DEFAULT_PRODUCT_TYPE];

        if (!($dataMapper instanceof DataMapperInterface)) {
            throw new ConfigurationMismatchException(
                __(
                    'Data mapper "%1" must implement interface %2',
                    get_class($dataMapper),
                    DataMapperInterface::class
                )
            );
        }

        return $dataMapper;
    }
}
