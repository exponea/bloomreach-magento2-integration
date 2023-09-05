<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure\Catalog;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapperResolver;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory;
use Bloomreach\EngagementConnector\Service\Integration\CreateCatalog;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for creating a product catalog
 */
class Create
{
    /**
     * @var CatalogNameGetter
     */
    private $catalogNameGetter;

    /**
     * @var CreateCatalog
     */
    private $createCatalog;

    /**
     * @var CollectionFactory
     */
    private $entityCollectionFactory;

    /**
     * @var DataMapperResolver
     */
    private $dataMapperResolver;

    /**
     * @var FieldsMapper
     */
    private $fieldsMapper;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @param CatalogNameGetter $catalogNameGetter
     * @param CreateCatalog $createCatalog
     * @param CollectionFactory $entityCollectionFactory
     * @param DataMapperResolver $dataMapperResolver
     * @param FieldsMapper $fieldsMapper
     * @param EntityType $entityType
     */
    public function __construct(
        CatalogNameGetter $catalogNameGetter,
        CreateCatalog $createCatalog,
        CollectionFactory $entityCollectionFactory,
        DataMapperResolver $dataMapperResolver,
        FieldsMapper $fieldsMapper,
        EntityType $entityType
    ) {
        $this->catalogNameGetter = $catalogNameGetter;
        $this->createCatalog = $createCatalog;
        $this->entityCollectionFactory = $entityCollectionFactory;
        $this->dataMapperResolver = $dataMapperResolver;
        $this->fieldsMapper = $fieldsMapper;
        $this->entityType = $entityType;
    }

    /**
     * Creates Catalog
     *
     * @param string $entityType
     *
     * @return string
     * @throws LocalizedException
     */
    public function execute(string $entityType): string
    {
        return $this->createCatalog->execute($this->buildBody($entityType));
    }

    /**
     * Builds request body
     *
     * @param string $entityType
     *
     * @return array
     * @throws LocalizedException
     */
    private function buildBody(string $entityType): array
    {
        return [
            'name' => $this->catalogNameGetter->execute($entityType),
            'is_product_catalog' => true,
            'fields' => $this->getFields($entityType)
        ];
    }

    /**
     * Get catalog fields
     *
     * @param string $entityType
     *
     * @return array
     * @throws LocalizedException
     */
    private function getFields(string $entityType): array
    {
        $collection = $this->entityCollectionFactory->create($entityType);
        $collection->setPageSize(1);
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

        return $this->fieldsMapper->map(
            $this->dataMapperResolver->map($collection->getFirstItem(), $entityType)->toArray()
        );
    }
}
