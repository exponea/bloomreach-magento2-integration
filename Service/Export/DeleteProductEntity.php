<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddDeleteActionToExportQueue;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for adding delete action to the export queue
 */
class DeleteProductEntity
{
    /**
     * @var AddDeleteActionToExportQueue
     */
    private $addDeleteActionToExportQueue;

    /**
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AddDeleteActionToExportQueue $addDeleteActionToExportQueue
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddDeleteActionToExportQueue $addDeleteActionToExportQueue,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed,
        LoggerInterface $logger
    ) {
        $this->addDeleteActionToExportQueue = $addDeleteActionToExportQueue;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
        $this->logger = $logger;
    }

    /**
     * Add delete action for catalog_product and catalog_product_variants entity types
     *
     * @param ProductInterface $product
     *
     * @return void
     */
    public function execute(ProductInterface $product): void
    {
        $productId = (string) $product->getId();

        if (!in_array($product->getTypeId(), [Configurable::TYPE_CODE, BundleType::TYPE_CODE, Grouped::TYPE_CODE])
            && $this->isRealTimeUpdateAllowed->execute(ProductVariantsType::ENTITY_TYPE)
        ) {
            $this->addToExportQueue(ProductVariantsType::ENTITY_TYPE, $productId);
        }

        if ($this->isRealTimeUpdateAllowed->execute(DefaultType::ENTITY_TYPE)) {
            $this->addToExportQueue(DefaultType::ENTITY_TYPE, $productId);
        }
    }

    /**
     * Add data to the export queue
     *
     * @param string $entityType
     * @param string $productId
     *
     * @return void
     */
    private function addToExportQueue(string $entityType, string $productId): void
    {
        try {
            $this->addDeleteActionToExportQueue->execute(
                $entityType,
                $productId
            );
        } catch (\Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding delete action for entity with ID: %1'
                    . ' to the export queue for %2 entity type. Error: %3',
                    $productId,
                    $entityType,
                    $e->getMessage()
                )
            );
        }
    }
}
