<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\Export;

use Bloomreach\EngagementConnector\Model\Export\Queue\AddEventToExportQueue;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type as SimpleType;
use Magento\Downloadable\Model\Product\Type;
use Psr\Log\LoggerInterface;

/**
 * The class responsible to preparing product entity data after save
 */
class PrepareProductDataService
{
    private const ENTITY_TYPE = 'catalog_product';
    private const VARIANTS_TYPE = 'catalog_product_variants';

    /**
     * @var AddEventToExportQueue
     */
    private $addEventToExportQueue;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param AddEventToExportQueue $addEventToExportQueue
     * @param LoggerInterface $logger
     */
    public function __construct(
        AddEventToExportQueue $addEventToExportQueue,
        LoggerInterface $logger
    ) {
        $this->addEventToExportQueue = $addEventToExportQueue;
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

            if (in_array($product->getTypeId(), [
                SimpleType::TYPE_SIMPLE,
                SimpleType::TYPE_VIRTUAL,
                Type::TYPE_DOWNLOADABLE
            ])) {
                $catalogTypes[] = self::VARIANTS_TYPE;
            }

            if ($product->isVisibleInSiteVisibility()) {
                $catalogTypes[] = self::ENTITY_TYPE;
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
