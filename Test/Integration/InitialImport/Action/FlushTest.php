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
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Enable;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Flush;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection as ExportQueueCollection;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use Bloomreach\EngagementConnector\Service\Integration\CreateCatalog;
use Bloomreach\EngagementConnector\Service\Integration\CreateImport;
use Bloomreach\EngagementConnector\Service\Integration\DeleteCatalog;
use Bloomreach\EngagementConnector\Service\Integration\DeleteImport;
use Bloomreach\EngagementConnector\Service\Integration\Response\ResponseValidator;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Exception;
use Magento\Framework\Exception\LocalizedException;

/**
 * Cover flush import action with integration tests
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FlushTest extends AbstractActionTestCase
{
    /**
     * @var Flush
     */
    private $flushAction;

    /**
     * @var Enable
     */
    private $enableAction;

    /**
     * @var Configure
     */
    private $configureAction;

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
     * @return void
     */
    public function testSuccess(): void
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
        //Create import
        $this->enableAction->execute($entityType);
        $this->scopeConfig->clean();
        $this->configureAction->execute($entityType);
        $this->scopeConfig->clean();
        //Set initial import status
        $initialExportStatus = $this->initialExportStatusGetter->execute($entityType);
        $initialExportStatus->setStatus(StatusSource::SUCCESS);
        $this->saveInitialExportStatus->execute($initialExportStatus);
        $this->scopeConfig->clean();
        //Execute flush action
        $this->executeAction($entityType);
        $this->scopeConfig->clean();
        $this->assertEquals(
            StatusSource::NOT_READY,
            $this->initialExportStatusGetter->execute(DefaultType::ENTITY_TYPE)->getStatus()
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
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 0
     *
     * @return void
     */
    public function testValidationExceptionWithDisabledStatus(): void
    {
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, 'Disabled');
    }

    /**
     *  Test validation exception when import status is "Not Ready"
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
     * Skip test execution
     *
     * @dataProvider initialImportStatusDataProvider
     *
     * @param string $statusLabel
     * @param int $status
     *
     * @return void
     */
    public function testValidationException(string $statusLabel, int $status): void
    {
        $this->assertIsInt($status);
    }

    /**
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableAction = $this->objectManager->get(Enable::class);
        $this->configureAction = $this->objectManager->get(Configure::class);
        $this->flushAction = $this->objectManager->get(Flush::class);
        $this->exportQueueResource = $this->objectManager->create(ExportQueueResource::class);
        $this->addInitialExportDataToExportQueue = $this->objectManager->get(
            AddInitialExportDataToExportQueue::class
        );
    }

    /**
     * Configure object manager
     *
     * @return void
     */
    protected function configureObjectManager(): void
    {
        parent::configureObjectManager();
        $createImportMock = $this->getMockBuilder(CreateImport::class)
            ->setConstructorArgs(
                [
                    $this->objectManager->get(ConfigProvider::class),
                    $this->objectManager->get(RequestSender::class),
                    $this->objectManager->get(ResponseValidator::class)
                ]
            )
            ->getMock();
        $createImportMock->expects($this->any())->method('execute')->willReturn(uniqid());
        $this->objectManager->addSharedInstance($createImportMock, CreateImport::class);
        $createCatalogMock = $this->getMockBuilder(CreateCatalog::class)
            ->setConstructorArgs(
                [
                    $this->objectManager->get(ConfigProvider::class),
                    $this->objectManager->get(RequestSender::class),
                    $this->objectManager->get(ResponseValidator::class)
                ]
            )
            ->getMock();
        $createCatalogMock->expects($this->any())->method('execute')->willReturn(uniqid());
        $this->objectManager->addSharedInstance($createCatalogMock, CreateCatalog::class);
        $deleteImportMock = $this->getMockBuilder(DeleteImport::class)
            ->setConstructorArgs(
                [
                    $this->objectManager->get(ConfigProvider::class),
                    $this->objectManager->get(RequestSender::class),
                    $this->objectManager->get(ResponseValidator::class)
                ]
            )
            ->getMock();
        $deleteImportMock->expects($this->any())->method('execute')->willReturn(true);
        $this->objectManager->addSharedInstance($deleteImportMock, DeleteImport::class);
        $deleteCatalogMock = $this->getMockBuilder(DeleteCatalog::class)
            ->setConstructorArgs(
                [
                    $this->objectManager->get(ConfigProvider::class),
                    $this->objectManager->get(RequestSender::class),
                    $this->objectManager->get(ResponseValidator::class)
                ]
            )
            ->getMock();
        $deleteCatalogMock->expects($this->any())->method('execute')->willReturn(true);
        $this->objectManager->addSharedInstance($deleteCatalogMock, DeleteCatalog::class);
    }

    /**
     * Execute enable action
     *
     * @param string $entityType
     *
     * @return void
     * @throws LocalizedException
     */
    protected function executeAction(string $entityType): void
    {
        $this->flushAction->execute($entityType);
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
        return 'Unable to flush import. Current import status: ' . $statusLabel;
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
