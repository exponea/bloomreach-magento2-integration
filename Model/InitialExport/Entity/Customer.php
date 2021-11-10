<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Entity;

use Bloomreach\EngagementConnector\Model\Export\Entity\AddMultipleToExport;
use Bloomreach\EngagementConnector\Model\InitialExport\InitialEntityExportInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\GetEntityIds;
use Bloomreach\EngagementConnector\Model\ResourceModel\GetEntityIdsFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for adding customer entity to the initial export
 */
class Customer implements InitialEntityExportInterface
{
    /**
     * @var AddMultipleToExport
     */
    private $addMultipleToExport;

    /**
     * @var GetEntityIdsFactory
     */
    private $getEntityIdsFactory;

    /**
     * @param GetEntityIdsFactory $getEntityIdsFactory
     * @param AddMultipleToExport $addMultipleToExport
     */
    public function __construct(
        GetEntityIdsFactory $getEntityIdsFactory,
        AddMultipleToExport $addMultipleToExport
    ) {
        $this->addMultipleToExport = $addMultipleToExport;
        $this->getEntityIdsFactory = $getEntityIdsFactory;
    }

    /**
     * Adds customer entity to the initial export
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        /** @var GetEntityIds $getEntityIds */
        $getEntityIds = $this->getEntityIdsFactory->create();
        $getEntityIds->setPrimaryColumn('entity_id');
        $getEntityIds->setTableName('customer_entity');
        
        foreach ($getEntityIds->execute() as $batchOfIds) {
            $this->addMultipleToExport->execute('customer', $batchOfIds);
        }
    }
}
