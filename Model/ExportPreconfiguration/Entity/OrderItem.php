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
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for order item entity export preconfiguration
 */
class OrderItem implements PreconfigurateEntityExportInterface
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
        /** @var PreconfigurationResultInterface $preconfigurationResult */
        $preconfigurationResult = $this->preconfigurationResultFactory->create();
        $preconfigurationResult->setEntityName('Order Item');
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setPageSize(1);
        $collection->addAttributeToSelect('*');
        $orderItem = $collection->getFirstItem();

        if (!$orderItem->getItemId()) {
            $preconfigurationResult->setError(true);
            $preconfigurationResult->setBody(
                __(
                    'You have no orders. Create one and try again'
                )->render()
            );

            return $preconfigurationResult;
        }

        try {
            $preconfigurationResult->setBody(
                $this->dummyFileGenerator->execute($orderItem, 'purchase_item')
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while generating dummy file for Order Item entity type. Error: %1',
                    $e->getMessage()
                )
            );

            $preconfigurationResult->setError(true);
            $preconfigurationResult->setBody(
                __(
                    'An error occurred while generating dummy file for Order Item entity type. ' .
                    'Contact technical support.'
                )->render()
            );
        }

        return $preconfigurationResult;
    }
}
