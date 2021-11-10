<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\Export\Entity;

use Bloomreach\EngagementConnector\Model\Export\Entity\CollectionFactory;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddDataToExportQueue;
use InvalidArgumentException;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;

/**
 * This class is responsible for obtaining a collection of entities and adds them to the export queue
 */
class AddToExportQueue
{
    /**
     * @var AddDataToExportQueue
     */
    private $addDataToExportQueue;

    /**
     * @var CollectionFactory
     */
    private $entityCollectionFactory;

    /**
     * @param AddDataToExportQueue $addDataToExportQueue
     * @param CollectionFactory $entityCollectionFactory
     */
    public function __construct(
        AddDataToExportQueue $addDataToExportQueue,
        CollectionFactory $entityCollectionFactory
    ) {
        $this->addDataToExportQueue = $addDataToExportQueue;
        $this->entityCollectionFactory = $entityCollectionFactory;
    }

    /**
     * Adds entities to the export queue
     *
     * @param string $entityType
     * @param array $exportEntityIds
     *
     * @return void
     * @throws ConfigurationMismatchException
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws NotFoundException
     */
    public function execute(string $entityType, array $exportEntityIds): void
    {
        $collection = $this->entityCollectionFactory->create($entityType);
        $collection->addFieldToFilter($collection->getResource()->getIdFieldName(), ['in' => $exportEntityIds]);
        $collection->addAttributeToSelect('*');
        $this->addDataToExportQueue->execute($entityType, $collection->getItems());
    }
}
