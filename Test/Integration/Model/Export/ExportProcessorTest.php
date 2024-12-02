<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Model\Export;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\SaveExportQueueInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsRealTimeUpdateAllowed;
use Bloomreach\EngagementConnector\Model\Export\ExportProcessor;
use Bloomreach\EngagementConnector\Model\Export\Queue\AddInitialExportDataToExportQueue;
use Bloomreach\EngagementConnector\Model\Export\Transporter\TransporterResolver;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Covers Export processor
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExportProcessorTest extends AbstractProcessorTestCase
{
    private const EVENT_API_TYPE = 'event';

    /**
     * @var ExportProcessor
     */
    private $exportProcessor;

    /**
     * @var SaveExportQueueInterface
     */
    private $saveExportQueue;

    /**
     * Covers success csv export
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSuccessCsvExport(): void
    {
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::PROCESSING, false, 1);
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            AddInitialExportDataToExportQueue::API_TYPE,
            ExportQueueModel::STATUS_NEW,
            0,
            time() - 100000,
            1
        );
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            self::EVENT_API_TYPE,
            ExportQueueModel::STATUS_NEW,
            0
        );
        $this->exportProcessor->process();
        $exportQueueItem = $this->getExportQueueItem(
            DefaultType::ENTITY_TYPE,
            AddInitialExportDataToExportQueue::API_TYPE
        );
        $this->assertExportQueueItem($exportQueueItem, ExportQueueModel::STATUS_COMPLETE, 1);
        $exportQueueItem = $this->getExportQueueItem(DefaultType::ENTITY_TYPE, self::EVENT_API_TYPE);
        $this->assertExportQueueItem($exportQueueItem, ExportQueueModel::STATUS_NEW, 0);
        $initialExportStatus = $this->initialExportStatusGetter->execute(DefaultType::ENTITY_TYPE);
        $this->assertEquals(StatusSource::SUCCESS, $initialExportStatus->getStatus());
        $this->assertEquals(1, $initialExportStatus->getTotalExported());
    }

    /**
     * Covers success event export
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSuccessEventExport(): void
    {
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::SUCCESS, false, 1);
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            AddInitialExportDataToExportQueue::API_TYPE,
            ExportQueueModel::STATUS_COMPLETE,
            1
        );
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            self::EVENT_API_TYPE,
            ExportQueueModel::STATUS_NEW,
            0
        );
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            self::EVENT_API_TYPE,
            ExportQueueModel::STATUS_ERROR,
            1,
            time() - 10000
        );
        $this->exportProcessor->process();
        $exportQueueItems = $this->getExportQueueItems(DefaultType::ENTITY_TYPE, self::EVENT_API_TYPE);

        $retries = 1;

        foreach ($exportQueueItems->getItems() as $exportQueueItem) {
            $this->assertExportQueueItem($exportQueueItem, ExportQueueModel::STATUS_COMPLETE, $retries++);
        }
    }

    /**
     * Covers case when Initial Import is locked
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfInitialImportIsLocked(): void
    {
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::PROCESSING, true);
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            self::EVENT_API_TYPE,
            ExportQueueModel::STATUS_NEW,
            0
        );
        $this->exportProcessor->process();
        $exportQueueItem = $this->getExportQueueItem(DefaultType::ENTITY_TYPE, self::EVENT_API_TYPE);
        $this->assertExportQueueItem($exportQueueItem, ExportQueueModel::STATUS_NEW, 0);
    }

    /**
     * Covers case when retry count exceeded
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfRetryCountExceeded(): void
    {
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::SUCCESS);
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            self::EVENT_API_TYPE,
            ExportQueueModel::STATUS_ERROR,
            5
        );
        $this->exportProcessor->process();
        $exportQueueItem = $this->getExportQueueItem(DefaultType::ENTITY_TYPE, self::EVENT_API_TYPE);
        $this->assertExportQueueItem($exportQueueItem, ExportQueueModel::STATUS_ERROR, 5);
    }

    /**
     * Covers case when sending attempt time is not reached
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfTimeOfSendingAttemptTimeIsNotReached(): void
    {
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::SUCCESS);
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            self::EVENT_API_TYPE,
            ExportQueueModel::STATUS_ERROR,
            2,
            time() + 10000
        );
        $this->exportProcessor->process();
        $exportQueueItem = $this->getExportQueueItem(DefaultType::ENTITY_TYPE, self::EVENT_API_TYPE);
        $this->assertExportQueueItem($exportQueueItem, ExportQueueModel::STATUS_ERROR, 2);
    }

    /**
     * Covers case when item status is not allowed
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     * @dataProvider notAllowedStatusesDataProvider
     *
     * @param int $status
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfStatusIsNotAllowed(int $status): void
    {
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::SUCCESS);
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            self::EVENT_API_TYPE,
            $status,
            2,
            time() - 10000
        );
        $this->exportProcessor->process();
        $exportQueueItem = $this->getExportQueueItem(DefaultType::ENTITY_TYPE, self::EVENT_API_TYPE);
        $this->assertExportQueueItem($exportQueueItem, $status, 2);
    }

    /**
     * Covers case when feed is disabled
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 0
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfCsvExportAndFeedIsDisabled(): void
    {
        $this->assertExportItemSendingIsNotAllowed(AddInitialExportDataToExportQueue::API_TYPE);
    }

    /**
     * Covers case when import ID is empty
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfCsvExportAndImportIdIsEmpty(): void
    {
        $this->assertExportItemSendingIsNotAllowed(AddInitialExportDataToExportQueue::API_TYPE);
    }

    /**
     * Covers case when feed is disabled
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 0
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfEventExportAndFeedIsDisabled(): void
    {
        $this->assertExportItemSendingIsNotAllowed(self::EVENT_API_TYPE);
    }

    /**
     * Covers case when Import ID is empty
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfEventExportAndImportIdIsEmpty(): void
    {
        $this->assertExportItemSendingIsNotAllowed(self::EVENT_API_TYPE);
    }

    /**
     * Covers case when Catalog ID is empty
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfEventExportAndCatalogIdIsEmpty(): void
    {
        $this->assertExportItemSendingIsNotAllowed(self::EVENT_API_TYPE);
    }

    /**
     * Covers case when Real time update is disabled
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/catalog_id test_import_id
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/real_time_updates 0
     *
     * @return void
     * @throws LocalizedException
     */
    public function testSkipIfEventExportAndRealTimeUpdateIsDisabled(): void
    {
        $this->assertExportItemSendingIsNotAllowed(self::EVENT_API_TYPE);
    }

    /**
     * Not allowed statuses data provider
     *
     * @return array[]
     */
    public function notAllowedStatusesDataProvider(): array
    {
        return [
            [
                'status' => ExportQueueModel::STATUS_COMPLETE
            ],
            [
                'status' => ExportQueueModel::STATUS_IN_PROGRESS
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
        $this->exportProcessor = $this->objectManager->get(ExportProcessor::class);
        $this->saveExportQueue = $this->objectManager->get(SaveExportQueueInterface::class);
    }

    /**
     * Configure object manager
     *
     * @return void
     */
    protected function configureObjectManager(): void
    {
        parent::configureObjectManager();
        $this->objectManager->configure(
            [
                IsRealTimeUpdateAllowed::class => [
                    'arguments' => [
                        'useCache' => false
                    ]
                ]
            ]
        );
        $transporterMock = $this->getMockBuilder(TransporterResolver::class)
            ->setConstructorArgs(
                [
                    []
                ]
            )
            ->getMock();
        $transporterMock->expects($this->any())->method('send')->willReturn(true);
        $this->objectManager->addSharedInstance($transporterMock, TransporterResolver::class);
    }

    /**
     * Assert Export Item sending is not allowed
     *
     * @param string $apiType
     *
     * @return void
     * @throws LocalizedException
     */
    private function assertExportItemSendingIsNotAllowed(string $apiType): void
    {
        $this->createInitialExportStatus(DefaultType::ENTITY_TYPE, StatusSource::SUCCESS);
        $this->createExportQueueItem(
            DefaultType::ENTITY_TYPE,
            $apiType,
            ExportQueueModel::STATUS_NEW,
            0
        );
        $this->exportProcessor->process();
        $exportQueueItem = $this->getExportQueueItem(DefaultType::ENTITY_TYPE, $apiType);
        $this->assertExportQueueItem($exportQueueItem, ExportQueueModel::STATUS_NEW, 0);
    }

    /**
     * Assert export queue item
     *
     * @param ExportQueueInterface $exportQueueItem
     * @param int $status
     * @param int $retries
     *
     * @return void
     */
    private function assertExportQueueItem(ExportQueueInterface $exportQueueItem, int $status, int $retries): void
    {
        $this->assertEquals(
            $status,
            $exportQueueItem->getStatus(),
        );
        $this->assertEquals(
            $retries,
            $exportQueueItem->getRetries()
        );
    }

    /**
     * Get export queue items
     *
     * @param string $entityType
     * @param string $apiType
     *
     * @return Collection
     */
    private function getExportQueueItems(string $entityType, string $apiType): Collection
    {
        return $this->getExportQueueCollection()
            ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, $entityType)
            ->addFieldToFilter(ExportQueueModel::API_TYPE, $apiType);
    }

    /**
     * Get export queue items
     *
     * @param string $entityType
     * @param string $apiType
     *
     * @return ExportQueueInterface
     */
    private function getExportQueueItem(string $entityType, string $apiType): ExportQueueInterface
    {
        return $this->getExportQueueItems($entityType, $apiType)->getFirstItem();
    }

    /**
     * Create export queue item
     *
     * @param string $entityType
     * @param string $apiType
     * @param int $status
     * @param int $retries
     * @param int $timeOfNextSending
     * @param int $numberOfItems
     *
     * @return void
     * @throws CouldNotSaveException
     */
    private function createExportQueueItem(
        string $entityType,
        string $apiType,
        int $status,
        int $retries,
        int $timeOfNextSending = 0,
        int $numberOfItems = 0
    ): void {
        $exportQueueItem = $this->objectManager->create(ExportQueueInterface::class);
        $exportQueueItem->setEntityType($entityType);
        $exportQueueItem->setApiType($apiType);
        $exportQueueItem->setStatus($status);
        $exportQueueItem->setRetries($retries);
        $exportQueueItem->setTimeOfNextSendingAttempt($timeOfNextSending);
        $exportQueueItem->setNumberOfItems($numberOfItems);
        $exportQueueItem->setBody(uniqid());
        $this->saveExportQueue->execute($exportQueueItem);
    }
}
