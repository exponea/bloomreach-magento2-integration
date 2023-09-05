<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterfaceFactory;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\InitialExportStatus;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatus\Collection;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\ResourceModel\InitialExportStatus\CollectionFactory;

/**
 * The class is responsible for obtaining InitialExportStatus list
 */
class ItemsGetter
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var InitialExportStatusInterfaceFactory
     */
    private $initialExportStatusFactory;

    /**
     * @var EntityType
     */
    private $entityType;

    /**
     * @var ItemUpdater
     */
    private $itemUpdater;

    /**
     * @param CollectionFactory $collectionFactory
     * @param InitialExportStatusInterfaceFactory $initialExportStatusFactory
     * @param EntityType $entityType
     * @param ItemUpdater $itemUpdater
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        InitialExportStatusInterfaceFactory $initialExportStatusFactory,
        EntityType $entityType,
        ItemUpdater $itemUpdater
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->initialExportStatusFactory = $initialExportStatusFactory;
        $this->entityType = $entityType;
        $this->itemUpdater = $itemUpdater;
    }

    /**
     * Get list of InitialExportStatus
     *
     * @return InitialExportStatusInterface[]
     */
    public function execute(): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(InitialExportStatus::ENTITY_TYPE, ['in' => $this->entityType->getAllTypes()]);

        $items = [];

        /** @var InitialExportStatusInterface $item */
        foreach ($collection->getItems() as $item) {
            $items[$item->getEntityType()] = $item;
        }

        foreach ($this->entityType->getAllTypes() as $entityType) {
            $initialExportStatus = $items[$entityType] ?? $this->initialExportStatusFactory->create();
            $initialExportStatus->setEntityType($entityType);
            $items[$entityType] = $this->itemUpdater->execute($initialExportStatus);
        }

        return $items;
    }
}
