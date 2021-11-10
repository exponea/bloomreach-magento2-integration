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
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for adding order entity to the initial export
 */
class Product implements InitialEntityExportInterface
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
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param AddMultipleToExport $addMultipleToExport
     * @param GetEntityIdsFactory $getEntityIdsFactory
     * @param ResourceConnection $resourceConnection
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        AddMultipleToExport $addMultipleToExport,
        GetEntityIdsFactory $getEntityIdsFactory,
        ResourceConnection $resourceConnection,
        ProductMetadataInterface $productMetadata
    ) {
        $this->addMultipleToExport = $addMultipleToExport;
        $this->getEntityIdsFactory = $getEntityIdsFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Adds catalog product entity to the initial export
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(): void
    {
        /** @var GetEntityIds $getEntityIds */
        $getEntityIds = $this->getEntityIdsFactory->create();
        $getEntityIds->setPrimaryColumn('entity_id');
        $getEntityIds->setTableName('catalog_product_entity');

        $catalogProductEntityInt = $this->resourceConnection->getTableName('catalog_product_entity_int');
        $eavAttribute = $this->resourceConnection->getTableName('eav_attribute');
        $catalogProductEntity = $this->resourceConnection->getTableName('catalog_product_entity');

        $primaryKey = $this->getPrimaryKey();
        $condition1 = $catalogProductEntityInt .'.`' . $primaryKey . '` = ' . $catalogProductEntity . '.`' . $primaryKey
                    . '` AND ' . $catalogProductEntityInt . '.`value` IN ("2","3","4")';
        $condition2 = $catalogProductEntityInt . '.`attribute_id` = ' . $eavAttribute. '.`attribute_id` AND '
                    .  $eavAttribute . '.`attribute_code` = "visibility"';

        $getEntityIds->setInnerJoin(
            [
                [
                    'table' => $catalogProductEntityInt,
                    'condition' => $condition1
                ],
                [
                    'table' => $eavAttribute,
                    'condition' => $condition2
                ]
            ]
        );

        foreach ($getEntityIds->execute() as $batchOfIds) {
            $this->addMultipleToExport->execute('catalog_product', $batchOfIds);
        }
    }

    /**
     * Returns primary key for Magento Edition
     *
     * @return string
     */
    private function getPrimaryKey(): string
    {
        return $this->productMetadata->getEdition() !== ProductMetadata::EDITION_NAME ? 'row_id' : 'entity_id';
    }
}
