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
use Bloomreach\EngagementConnector\Model\Export\Entity\ProductVariantsCollection;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddEventToExportQueue;
use Magento\Catalog\Api\Data\ProductInterface;
use Psr\Log\LoggerInterface;

/**
 * The class responsible to preparing product entity data after save
 */
class PrepareProductDataService
{

    /**
     * @var AddEventToExportQueue
     */
    private $addEventToExportQueue;

    /**
     * @var IsRealTimeUpdateAllowed
     */
    private $isRealTimeUpdateAllowed;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AddEventToExportQueue $addEventToExportQueue
     * @param IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddEventToExportQueue $addEventToExportQueue,
        IsRealTimeUpdateAllowed $isRealTimeUpdateAllowed,
        LoggerInterface $logger
    ) {
        $this->addEventToExportQueue = $addEventToExportQueue;
        $this->isRealTimeUpdateAllowed = $isRealTimeUpdateAllowed;
        $this->logger = $logger;
    }

    /**
     * Preparing product entity data after save
     *
     * @param ProductInterface $product
     *
     * @return void
     */
    public function execute(ProductInterface $product): void
    {
        try {
            $catalogTypes = [];

            if (in_array($product->getTypeId(), ProductVariantsCollection::VARIANT_TYPES)
                && $this->isRealTimeUpdateAllowed->execute(ProductVariantsType::ENTITY_TYPE)
            ) {
                $catalogTypes[] = ProductVariantsType::ENTITY_TYPE;
            }

            if ($product->isVisibleInSiteVisibility()
                && $this->isRealTimeUpdateAllowed->execute(DefaultType::ENTITY_TYPE)
            ) {
                $catalogTypes[] = DefaultType::ENTITY_TYPE;
            }

            foreach ($catalogTypes as $catalogType) {
                $this->addEventToExportQueue->execute(
                    $catalogType,
                    '',
                    $product
                );
            }
        } catch (\Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while adding Product update event to the export queue. Error: %1',
                    $e->getMessage()
                )
            );
        }
    }
}
