<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataMapping;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\DataMapperInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Data mapper factory uses to create appropriate mapper class
 */
class DataMapperFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string[]
     */
    private $dataMappers;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string[] $dataMappers
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $dataMappers = []
    ) {
        $this->objectManager = $objectManager;
        $this->dataMappers = $dataMappers;
    }

    /**
     * Create instance of data mapper for specified entity type
     *
     * @param string $entityType
     * @return DataMapperInterface
     * @throws NoSuchEntityException
     * @throws ConfigurationMismatchException
     */
    public function create(string $entityType): DataMapperInterface
    {
        if (!isset($this->dataMappers[$entityType])) {
            throw new NoSuchEntityException(
                __(
                    'There is no such data mapper "%1" for interface %2',
                    $entityType,
                    DataMapperInterface::class
                )
            );
        }
        $dataMapperClass = $this->dataMappers[$entityType];
        $dataMapperEntity = $this->objectManager->create($dataMapperClass);
        if (!$dataMapperEntity instanceof DataMapperInterface) {
            throw new ConfigurationMismatchException(
                __(
                    'Data mapper "%1" must implement interface %2',
                    $dataMapperClass,
                    DataMapperInterface::class
                )
            );
        }

        return $dataMapperEntity;
    }
}
