<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Controller;

use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\ExportProcessor;
use Bloomreach\EngagementConnector\Model\Export\File\DirectoryProvider;
use Bloomreach\EngagementConnector\Model\Export\QueueProcessor;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\InitialExport\InitialEntityExportInterface;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity\Collection as ExportEntityCollection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportEntity\CollectionFactory as ExportEntityCollectionFactory;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection as ExportQueueCollection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory as ExportQueueCollectionFactory;
use Exception;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Initial import test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RunInitialImportTest extends AbstractBackendController
{
    private const API_TYPE = 'csv_export';

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ExportQueueCollectionFactory
     */
    private $exportQueueCollectionFactory;

    /**
     * @var ExportEntityCollectionFactory
     */
    private $exportEntityCollectionFactory;

    /**
     * @var QueueProcessor
     */
    private $queueProcessor;

    /**
     * @var InitialEntityExportInterface
     */
    private $initialEntityExportInterface;

    /**
     * @var ExportProcessor
     */
    private $exportProcessor;

    /**
     * @var DirectoryProvider
     */
    private $directoryProvider;

    /**
     * @var File
     */
    private $file;

    /**
     * @var EntityType
     */
    private $entityTypes;

    /**
     * Initial import
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/order.php
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/customer.php
     *
     * @return void
     * @throws FileSystemException
     */
    public function testRunInitialImport(): void
    {
        $this->dispatchRunInitialImportRequest();
        $response = $this->jsonSerializer->unserialize((string) $this->getResponse()->getBody());
        $this->assertArrayHasKey('message', $response);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(0, (int) $response['error']);
        $this->assertEquals(
            'An import was successfully added to the queue. The data will soon be imported into Bloomreach.',
            $response['message']
        );

        $this->initialEntityExportInterface->execute();

        /** @var ExportEntityCollection $exportEntityCollection */
        $exportEntityCollection = $this->exportEntityCollectionFactory->create();
        $this->assertEquals(true, $exportEntityCollection->count() > 0);

        $this->queueProcessor->process();

        /** @var ExportEntityCollection $exportEntityCollection */
        $exportEntityCollection = $this->exportEntityCollectionFactory->create();
        $this->assertEquals(0, $exportEntityCollection->count());

        /** @var ExportQueueCollection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $exportQueueCollection->addFieldToFilter(ExportQueueModel::API_TYPE, self::API_TYPE);
        $this->assertEquals(true, $exportQueueCollection->count() > 0);

        $this->exportProcessor->process();

        /** @var ExportQueueCollection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $exportQueueCollection->addFieldToFilter(ExportQueueModel::API_TYPE, self::API_TYPE);
        $this->assertEquals(true, $exportQueueCollection->count() > 0);

        foreach ($exportQueueCollection->getItems() as $exportQueueItem) {
            $this->assertEquals(ExportQueueModel::STATUS_ERROR, (int) $exportQueueItem->getStatus());
            $this->assertEquals(1, (int) $exportQueueItem->getRetries());
        }

        foreach ($this->entityTypes->getAllTypes() as $entityType) {
            $this->assertEquals(true, $this->isFileExists($entityType));
        }
    }

    /**
     * Dispatch RunInitialImportReconfiguration controller
     *
     * @return void
     */
    private function dispatchRunInitialImportRequest(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/bloomreach_engagement/import/RunInitialImport');
    }

    /**
     * Checks is file exists
     *
     * @param string $entityType
     *
     * @return bool
     * @throws FileSystemException
     */
    private function isFileExists(string $entityType): bool
    {
        $path = $this->directoryProvider->getAbsolutePath($entityType);
        $files = $this->file->readDirectory($path);
        $filePath = current($files);
        $pathAsArray = explode('/', $filePath);
        $fileName = array_pop($pathAsArray);

        return strpos($fileName, '.csv') !== false;
    }

    /**
     * Test set up
     *
     * @return void
     * @throws AuthenticationException
     * @throws FileSystemException
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->jsonSerializer = $this->_objectManager->create(SerializerInterface::class);
        $this->initialEntityExportInterface = $this->_objectManager->create(InitialEntityExportInterface::class);
        $this->exportEntityCollectionFactory = $this->_objectManager->create(ExportEntityCollectionFactory::class);
        $this->exportQueueCollectionFactory = $this->_objectManager->create(ExportQueueCollectionFactory::class);
        $this->queueProcessor = $this->_objectManager->create(QueueProcessor::class);
        $this->exportProcessor = $this->_objectManager->create(ExportProcessor::class);
        $this->directoryProvider = $this->_objectManager->create(DirectoryProvider::class);
        $this->file = $this->_objectManager->create(File::class);
        $this->entityTypes = $this->_objectManager->create(EntityType::class);
        $this->clearExportEntity();
        $this->clearExportQueue();
        $this->clearExportFiles();
    }

    /**
     * Clear export entities
     *
     * @return void
     * @throws Exception
     */
    private function clearExportEntity(): void
    {
        /** @var ExportEntityCollection $exportEntityCollection */
        $exportEntityCollection = $this->exportEntityCollectionFactory->create();
        $exportEntityResourceModel = $exportEntityCollection->getResource();
        /** @var ExportQueueModel $exportQueueItem */
        foreach ($exportEntityCollection as $exportEntityItem) {
            $exportEntityResourceModel->delete($exportEntityItem);
        }
    }

    /**
     * Clear export queue
     *
     * @return void
     * @throws Exception
     */
    private function clearExportQueue(): void
    {
        /** @var ExportQueueCollection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $exportQueueResourceModel = $exportQueueCollection->getResource();
        /** @var ExportQueueModel $exportQueueItem */
        foreach ($exportQueueCollection as $exportQueueItem) {
            $exportQueueResourceModel->delete($exportQueueItem);
        }
    }

    /**
     * Clear export files
     *
     * @return void
     * @throws FileSystemException
     */
    private function clearExportFiles(): void
    {
        foreach ($this->entityTypes->getAllTypes() as $entityType) {
            $path = $this->directoryProvider->getAbsolutePath($entityType);
            $files = $this->file->readDirectory($path);
            foreach ($files as $filePath) {
                $this->file->deleteFile($filePath);
            }
        }
    }

    /**
     * Clear test data
     *
     * @return void
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearExportEntity();
        $this->clearExportQueue();
        $this->clearExportFiles();
    }
}
