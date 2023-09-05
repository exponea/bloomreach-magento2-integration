<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Import\Source\SourceInterfaceFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;

/**
 * The class is responsible for generating the import source
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceGenerator
{
    private const NUMBER_OF_ITEMS = 10;

    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

    /**
     * @var SampleFileGenerator
     */
    private $sampleFileGenerator;

    /**
     * @var SourceInterfaceFactory
     */
    private $sourceInterfaceFactory;

    /**
     * @var CollectionFactory
     */
    private $entityCollectionFactory;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var PropertyMapper
     */
    private $propertyMapper;

    /**
     * @param DataMapperResolver $dataMapperResolver
     * @param SampleFileGenerator $sampleFileGenerator
     * @param SourceInterfaceFactory $sourceInterfaceFactory
     * @param CollectionFactory $entityCollectionFactory
     * @param PropertyMapper $propertyMapper
     * @param EntityType $entityType
     */
    public function __construct(
        DataMapperResolver $dataMapperResolver,
        SampleFileGenerator $sampleFileGenerator,
        SourceInterfaceFactory $sourceInterfaceFactory,
        CollectionFactory $entityCollectionFactory,
        PropertyMapper $propertyMapper,
        EntityType $entityType
    ) {
        $this->dataMapperResolver = $dataMapperResolver;
        $this->sampleFileGenerator = $sampleFileGenerator;
        $this->sourceInterfaceFactory = $sourceInterfaceFactory;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->propertyMapper = $propertyMapper;
        $this->entityType = $entityType;
    }

    /**
     * Generate an empty import source
     * - Import file contains only headers
     *
     * @param string $entityType
     *
     * @return SourceInterface
     * @throws ConfigurationMismatchException
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     */
    public function generateEmpty(string $entityType): SourceInterface
    {
        /** @var SourceInterface $source */
        $source = $this->sourceInterfaceFactory->create();
        $data = $this->dataMapperResolver->map(
            $this->getCollection($entityType)->getFirstItem(),
            $entityType
        )->toArray();
        $source->setFileUrl($this->sampleFileGenerator->execute(array_keys($data), $entityType));
        $source->setPropertyMappings($this->propertyMapper->map($data));

        return $source;
    }

    /**
     * Generates an import source
     *
     * @param string $entityType
     *
     * @return SourceInterface
     * @throws ConfigurationMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws FileSystemException
     * @throws NotFoundException
     */
    public function generate(string $entityType): SourceInterface
    {
        /** @var SourceInterface $source */
        $source = $this->sourceInterfaceFactory->create();

        $data = [];

        foreach ($this->getCollection($entityType) as $item) {
            $data[] = $this->dataMapperResolver->map($item, $entityType)->toArray();
        }

        $source->setFileUrl($this->sampleFileGenerator->execute($data, $entityType));
        $source->setPropertyMappings($this->propertyMapper->map(current($data)));

        return $source;
    }

    /**
     * Get Collection by entity type
     *
     * @param string $entityType
     *
     * @return AbstractDb
     * @throws ConfigurationMismatchException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCollection(string $entityType): AbstractDb
    {
        $collection = $this->entityCollectionFactory->create($entityType);
        $collection->setPageSize(self::NUMBER_OF_ITEMS);
        $collection->addAttributeToSelect('*');

        if (!$collection->count()) {
            throw new LocalizedException(
                __(
                    'You have no items in %entity_type. Create one and try again',
                    [
                        'entity_type' => $this->entityType->getEntityName($entityType)
                    ]
                )
            );
        }

        return $collection;
    }
}
