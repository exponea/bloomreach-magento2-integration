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
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * The class is responsible for product entity export preconfiguration
 */
class Customer implements PreconfigurateEntityExportInterface
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
     * Returns preconfigured export data for customer entity
     *
     * @return PreconfigurationResultInterface
     */
    public function execute(): PreconfigurationResultInterface
    {
        /** @var PreconfigurationResultInterface $preconfigurationResult */
        $preconfigurationResult = $this->preconfigurationResultFactory->create();
        $preconfigurationResult->setEntityName('Customer');

        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->setPageSize(1);
        $customer = $collection->getFirstItem();

        if (!$customer->getEntityId()) {
            $preconfigurationResult->setError(true);
            $preconfigurationResult->setBody(
                __(
                    'You have no customers. Create one and try again'
                )->render()
            );

            return $preconfigurationResult;
        }

        try {
            $preconfigurationResult->setBody(
                $this->dummyFileGenerator->execute($customer, 'customer')
            );
        } catch (Exception $e) {
            $this->logger->error(
                __(
                    'An error occurred while generating dummy file for Customer entity type. Error: %1',
                    $e->getMessage()
                )
            );

            $preconfigurationResult->setError(true);
            $preconfigurationResult->setBody(
                __(
                    'An error occurred while generating dummy file for Customer entity type. ' .
                    'Contact technical support.'
                )->render()
            );
        }

        return $preconfigurationResult;
    }
}
