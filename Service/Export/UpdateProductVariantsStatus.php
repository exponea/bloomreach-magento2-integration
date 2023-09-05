<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\Export\Entity\ProductVariantsCollection;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddPartialDataToExportQueue;
use Bloomreach\EngagementConnector\Model\Product\ChildIdsDataProvider;
use Bloomreach\EngagementConnector\Model\Export\Entity\ProductVariantsCollectionFactory;
use Bloomreach\EngagementConnector\Service\Product\GetProductActiveStatus;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for updating product variants status after delete/change status parent product
 */
class UpdateProductVariantsStatus
{
    private const ITEM_ID = 'item_id';

    private const PRODUCT_ACTIVE = 'product_active';

    /**
     * @var ProductVariantsCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var GetProductActiveStatus
     */
    private $getProductActiveStatus;

    /**
     * @var ChildIdsDataProvider
     */
    private $childIdsDataProvider;

    /**
     * @var AddPartialDataToExportQueue
     */
    private $addPartialDataToExportQueue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ProductVariantsCollectionFactory $collectionFactory
     * @param GetProductActiveStatus $getProductActiveStatus
     * @param ChildIdsDataProvider $childIdsDataProvider
     * @param AddPartialDataToExportQueue $addPartialDataToExportQueue
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductVariantsCollectionFactory $collectionFactory,
        GetProductActiveStatus $getProductActiveStatus,
        ChildIdsDataProvider $childIdsDataProvider,
        AddPartialDataToExportQueue $addPartialDataToExportQueue,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->getProductActiveStatus = $getProductActiveStatus;
        $this->childIdsDataProvider = $childIdsDataProvider;
        $this->addPartialDataToExportQueue = $addPartialDataToExportQueue;
        $this->logger = $logger;
    }

    /**
     * Update variants status
     *
     * @param ProductInterface $product
     *
     * @return void
     */
    public function execute(ProductInterface $product): void
    {
        if (!in_array($product->getTypeId(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE, Grouped::TYPE_CODE])) {
            return;
        }

        $variantsCollection = $this->getVariantsCollection($product);

        /** @var ProductInterface $item */
        foreach ($variantsCollection as $variant) {
            $this->addToExportQueue($variant);
        }
    }

    /**
     * Add data to export queue
     *
     * @param ProductInterface $product
     *
     * @return void
     */
    private function addToExportQueue(ProductInterface $product): void
    {
        try {
            $this->addPartialDataToExportQueue->execute(
                ProductVariantsType::ENTITY_TYPE,
                $this->buildPartialData($product)
            );
        } catch (\Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding partial data for entity with ID: %1'
                    . ' to the export queue for %2 entity type. Error: %3',
                    $product->getId(),
                    ProductVariantsType::ENTITY_TYPE,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Builds partial data
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function buildPartialData(ProductInterface $product): array
    {
        return [
            self::ITEM_ID => $product->getId(),
            self::PRODUCT_ACTIVE => $this->getProductActiveStatus->execute($product)
        ];
    }

    /**
     * Retrieves product variants collection
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getVariantsCollection(ProductInterface $product): array
    {
        $childrenIds = $this->childIdsDataProvider->getIds($product);

        /** @var ProductVariantsCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect([ProductInterface::STATUS, ProductInterface::VISIBILITY]);
        $collection->addFieldToFilter(ProductInterface::VISIBILITY, Visibility::VISIBILITY_NOT_VISIBLE);
        $collection->addFieldToFilter('entity_id', ['in' => $childrenIds]);

        return $collection->getItems();
    }
}
