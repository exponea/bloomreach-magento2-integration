<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\ExportPreconfiguration\Entity;

use Bloomreach\EngagementConnector\Model\ExportPreconfiguration\DummyFileGenerator;
use Bloomreach\EngagementConnector\Model\ExportPreconfiguration\PreconfigurateEntityExportInterface;
use Bloomreach\EngagementConnector\Model\ExportPreconfiguration\PreconfigurationResultInterface;
use Bloomreach\EngagementConnector\Model\ExportPreconfiguration\PreconfigurationResultInterfaceFactory;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for product variants entity export preconfiguration
 */
class ProductVariants implements PreconfigurateEntityExportInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DummyFileGenerator
     */
    private $dummyFileGenerator;

    /**
     * @var PreconfigurationResultInterfaceFactory
     */
    private $preconfigurationResultFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CollectionFactory $collectionFactory
     * @param DummyFileGenerator $dummyFileGenerator
     * @param PreconfigurationResultInterfaceFactory $preconfigurationResultFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        DummyFileGenerator $dummyFileGenerator,
        PreconfigurationResultInterfaceFactory $preconfigurationResultFactory,
        LoggerInterface $logger
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->dummyFileGenerator = $dummyFileGenerator;
        $this->preconfigurationResultFactory = $preconfigurationResultFactory;
        $this->logger = $logger;
    }

    /**
     * Returns preconfigured export data for product entity
     *
     * @return PreconfigurationResultInterface
     */
    public function execute(): PreconfigurationResultInterface
    {
        /** @var PreconfigurationResultInterface $result */
        $result = $this->preconfigurationResultFactory->create();
        $result->setEntityName('Catalog Product Variants');
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter(ProductInterface::TYPE_ID, Type::TYPE_SIMPLE);
        $collection->setPageSize(1);
        $collection->addAttributeToSelect('*');
        $product = $collection->getFirstItem();

        if (!$product->getEntityId()) {
            $result->setError(true);
            $result->setBody(
                __(
                    'You have no products. Create one and try again'
                )->render()
            );

            return $result;
        }

        try {
            $result->setBody(
                $this->dummyFileGenerator->execute($product, 'catalog_product_variants')
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while generating dummy file for Catalog Product Variants entity type. Error: %1',
                    $e->getMessage()
                )
            );

            $result->setError(true);
            $result->setBody(
                __(
                    'An error occurred while generating dummy file for Catalog Product entity type. ' .
                    'Contact technical support.'
                )->render()
            );
        }

        return $result;
    }
}
