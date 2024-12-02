<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Model\Export;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Order;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\OrderItem;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\Export\QueueProcessor;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Magento\Framework\Exception\LocalizedException;

/**
 * Covers Queue processor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueueProcessorTest extends AbstractProcessorTestCase
{
    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * Covers processing only allowed entity
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_variants_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_variants_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 0
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/import_id test_import_id
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/order.php
     *
     * @return void
     */
    public function testProcessOnlyAllowedEntities(): void
    {
        //Not allowed status
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::PROCESSING, false, 10);
        //Is locked
        $this->createInitialExportStatus(ProductVariantsType::ENTITY_TYPE, StatusSource::SCHEDULED, true);
        //Feed is disabled
        $this->createInitialExportStatus(Customer::ENTITY_TYPE, StatusSource::SCHEDULED);
        //Import ID is missing
        $this->createInitialExportStatus(Order::ENTITY_TYPE, StatusSource::SCHEDULED);
        //Everything is fine
        $this->createInitialExportStatus(OrderItem::ENTITY_TYPE, StatusSource::SCHEDULED);
        //Execute Action
        $this->queueProcessor->process();
        //Assert initial export status
        $this->assertInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::PROCESSING, 10);
        $this->assertInitialExportStatus(ProductVariantsType::ENTITY_TYPE, StatusSource::SCHEDULED, 0);
        $this->assertInitialExportStatus(Customer::ENTITY_TYPE, StatusSource::DISABLED, 0);
        $this->assertInitialExportStatus(Order::ENTITY_TYPE, StatusSource::NOT_READY, 0);
        $this->assertInitialExportStatus(OrderItem::ENTITY_TYPE, StatusSource::PROCESSING, 1);
        //Assert export queue
        $this->assertExportQueueDoesNotContainEntityType(DefaultType::ENTITY_TYPE);
        $this->assertExportQueueDoesNotContainEntityType(ProductVariantsType::ENTITY_TYPE);
        $this->assertExportQueueDoesNotContainEntityType(Customer::ENTITY_TYPE);
        $this->assertExportQueueDoesNotContainEntityType(Order::ENTITY_TYPE);
        $this->assertExportQueue(OrderItem::ENTITY_TYPE, 1);
    }

    /**
     * Covers processing only one entity during iteration
     *
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/import_id test_import_id
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/order.php
     *
     * @return void
     * @throws LocalizedException
     */
    public function testProcessingOnlyOneEntity(): void
    {
        $this->createInitialExportStatus(Order::ENTITY_TYPE, StatusSource::SCHEDULED);
        $this->createInitialExportStatus(OrderItem::ENTITY_TYPE, StatusSource::SCHEDULED);
        $this->queueProcessor->process();
        $this->assertExportQueue(Order::ENTITY_TYPE, 1);
        $this->assertExportQueueDoesNotContainEntityType(OrderItem::ENTITY_TYPE);
    }

    /**
     * Covers with not allowed status
     *
     * @param int $status
     *
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/import_id test_import_id
     * @dataProvider initialExportStatusesDataProvider
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipNotAllowedStatuses(int $status): void
    {
        $this->createInitialExportStatus(OrderItem::ENTITY_TYPE, $status);
        $this->queueProcessor->process();
        $this->assertInitialExportStatus(OrderItem::ENTITY_TYPE, $status, 0);
        $this->assertExportQueueDoesNotContainEntityType(OrderItem::ENTITY_TYPE);
    }

    /**
     * Initial export statuses data provider
     *
     * @return array[]
     */
    public function initialExportStatusesDataProvider(): array
    {
        return [
            [
                'status' => StatusSource::PROCESSING
            ],
            [
                'status' => StatusSource::ERROR
            ],
            [
                'status' => StatusSource::SUCCESS
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
        $this->queueProcessor = $this->objectManager->get(QueueProcessor::class);
    }

    /**
     * Assert export queue
     *
     * @param string $entityType
     * @param int $numberOfItems
     *
     * @return void
     */
    private function assertExportQueue(string $entityType, int $numberOfItems): void
    {
        $exportQueueCollection = $this->getExportQueueCollection()
            ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, $entityType)
            ->addFieldToFilter(ExportQueueModel::API_TYPE, AddInitialExportDataToExportQueue::API_TYPE);
        $this->assertEquals(1, $exportQueueCollection->getSize());
        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getFirstItem();
        $this->assertEquals(ExportQueueModel::STATUS_NEW, $exportQueueItem->getStatus());
        $this->assertEquals(0, $exportQueueItem->getRetries());
        $this->assertEquals('.csv', mb_substr($exportQueueItem->getBody(), -4));
        $this->assertEmpty($exportQueueItem->getRegistered());
        $this->assertEquals($numberOfItems, $exportQueueItem->getNumberOfItems());
        $this->assertNotEmpty($exportQueueItem->getAdditionalData());
    }

    /**
     * Assert Export queue does not contain export entity
     *
     * @param string $entityType
     *
     * @return void
     */
    private function assertExportQueueDoesNotContainEntityType(string $entityType): void
    {
        $this->assertEquals(
            0,
            $this->getExportQueueCollection()
                ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, $entityType)
                ->addFieldToFilter(ExportQueueModel::API_TYPE, AddInitialExportDataToExportQueue::API_TYPE)
                ->getSize()
        );
    }

    /**
     * Assert initial export status
     *
     * @param string $entityType
     * @param int $status
     * @param int $totalItems
     *
     * @return void
     * @throws LocalizedException
     */
    private function assertInitialExportStatus(string $entityType, int $status, int $totalItems): void
    {
        $initialExportStatus = $this->initialExportStatusGetter->execute($entityType);
        $this->assertEquals($status, $initialExportStatus->getStatus());
        $this->assertEquals($totalItems, $initialExportStatus->getTotalItems());
    }
}
