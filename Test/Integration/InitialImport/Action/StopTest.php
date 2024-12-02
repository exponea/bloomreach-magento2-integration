<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\InitialImport\Action;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Stop;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection as ExportQueueCollection;
use Exception;
use Magento\Framework\Exception\LocalizedException;

/**
 * Cover enable import action with integration tests
 * @magentoAppArea adminhtml
 */
class StopTest extends AbstractActionTestCase
{
    /**
     * @var Stop
     */
    private $stopAction;

    /**
     * @var AddInitialExportDataToExportQueue
     */
    private $addInitialExportDataToExportQueue;

    /**
     * @var ExportQueueResource
     */
    private $exportQueueResource;

    /**
     * Test success case
     *
     * @param int $status
     *
     * @return void
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @dataProvider inProgressStatusDataProvider
     */
    public function testSuccess(int $status): void
    {
        $entityType = DefaultType::ENTITY_TYPE;
        $exportQueueCollection = $this->objectManager->create(ExportQueueCollection::class);
        //Add items to the export queue
        $this->addInitialExportDataToExportQueue->execute($entityType, 'test-data', 100, []);
        $this->addInitialExportDataToExportQueue->execute(Customer::ENTITY_TYPE, 'test-data', 100, []);
        $this->assertEquals(
            2,
            $exportQueueCollection->getSize()
        );

        //Set initial import status
        $initialExportStatus = $this->initialExportStatusGetter->execute($entityType);
        $initialExportStatus->setStatus($status);
        $this->saveInitialExportStatus->execute($initialExportStatus);
        //Execute action
        $this->executeAction($entityType);
        $this->assertEquals(
            StatusSource::READY,
            $this->initialExportStatusGetter->execute($entityType)->getStatus()
        );
        $this->assertEquals(
            1,
            $exportQueueCollection->clear()->getSize()
        );
        $this->assertEquals(
            0,
            $exportQueueCollection
                ->clear()
                ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, $entityType)
                ->getSize()
        );
    }

    /**
     * Test validation exception when import status is "Disabled"
     *
     * @return void
     */
    public function testValidationExceptionWithDisabledStatus(): void
    {
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, 'Disabled');
    }

    /**
     * Test validation exception when import status is "Not Ready"
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     *
     * @return void
     */
    public function testValidationExceptionWithNotReadyStatus(): void
    {
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, 'Not Ready');
    }

    /**
     * Test validation exception when import status is "Ready"
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     *
     * @return void
     */
    public function testValidationExceptionWithReadyStatus(): void
    {
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, 'Ready');
    }

    /**
     * Data provider
     *
     * @return array[]
     */
    public function initialImportStatusDataProvider(): array
    {
        return [
            [
                'statusLabel' => 'Error',
                'status' => StatusSource::ERROR
            ],
            [
                'statusLabel' => 'Success',
                'status' => StatusSource::SUCCESS
            ]
        ];
    }

    /**
     * In-progress statuses
     *
     * @return array[]
     */
    public function inProgressStatusDataProvider(): array
    {
        return [
            [
                'status' => StatusSource::SCHEDULED
            ],
            [
                'status' => StatusSource::PROCESSING
            ]
        ];
    }

    /**
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->stopAction = $this->objectManager->get(Stop::class);
        $this->exportQueueResource = $this->objectManager->create(ExportQueueResource::class);
        $this->addInitialExportDataToExportQueue = $this->objectManager->get(
            AddInitialExportDataToExportQueue::class
        );
    }

    /**
     * Execute action
     *
     * @param string $entityType
     *
     * @return void
     * @throws LocalizedException
     */
    protected function executeAction(string $entityType): void
    {
        $this->stopAction->execute($entityType);
    }

    /**
     * Get validation exception message
     *
     * @param string $statusLabel
     *
     * @return string
     */
    protected function getValidationExceptionMessage(string $statusLabel): string
    {
        return 'Unable to stop import. Current import status: ' . $statusLabel;
    }

    /**
     * Delete data after test
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        try {
            foreach ($this->objectManager->create(ExportQueueCollection::class)->getItems() as $exportQueue) {
                $this->exportQueueResource->delete($exportQueue);
            }
        } catch (Exception $e) {
            //Nothing to do
        }
    }
}
