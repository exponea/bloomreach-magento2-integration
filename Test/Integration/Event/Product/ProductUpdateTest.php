<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Event\Product;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Product update event test
 *
 * @magentoDbIsolation disabled
 * @magentoAppIsolation enabled
 * @magentoAppArea adminhtml
 */
class ProductUpdateTest extends TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var CollectionFactory
     */
    private $exportQueueCollectionFactory;

    /**
     * Test simple product update
     *
     * @magentoConfigFixture default/bloomreach_engagement/general/enable 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException
     */
    public function testSimpleProductUpdate(): void
    {
        // Update product name
        $product = $this->productRepository->get('simple');
        $product->setName('test');
        $this->productRepository->save($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(0, $exportQueueCollection->getSize());
    }

    /**
     * Test simple product update:
     *  - catalog_product_feed real time is enabled,
     *  - catalog_product_variants_feed real time is disabled.
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductRealTimeSimpleProductUpdate(): void
    {
        // Update product name
        $product = $this->productRepository->get('simple');
        $product->setName('test');
        $this->productRepository->save($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(1, $exportQueueCollection->getSize());
        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();
        $this->assertEventExportQueueItem($exportQueueItem, $product, 'catalog_product');
    }

    /**
     * Test simple product update:
     *  - catalog_product_feed real time is disabled,
     *  - catalog_product_variants_feed real time is enabled.
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/real_time_updates 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductVariationRealTimeSimpleProductUpdate(): void
    {
        // Update product name
        $product = $this->productRepository->get('simple');
        $product->setName('test');
        $this->productRepository->save($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(1, $exportQueueCollection->getSize());
        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();
        $this->assertEventExportQueueItem($exportQueueItem, $product, 'catalog_product_variants');
    }

    /**
     * Test simple product update:
     *  - catalog_product_feed real time is enabled,
     *  - catalog_product_variants_feed real time is enabled.
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/real_time_updates 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/real_time_updates 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testRealTimeSimpleProductUpdate(): void
    {
        // Update product name
        $product = $this->productRepository->get('simple');
        $product->setName('test');
        $this->productRepository->save($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(2, $exportQueueCollection->getSize());

        /** @var ExportQueueModel[] $items */
        $items = $exportQueueCollection->getItems();
        $this->assertEventExportQueueItem(array_shift($items), $product, 'catalog_product_variants');
        $this->assertEventExportQueueItem(array_shift($items), $product, 'catalog_product');
    }

    /**
     * Test configurable product update (real time related configs are disabled)
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/disabled_configurable_product_with_three_children.php
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testConfigurableProductUpdate(): void
    {
        // Update product name
        $product = $this->productRepository->get('configurable');
        $product->setName('test');
        $this->productRepository->save($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(0, $exportQueueCollection->getSize());
    }

    /**
     * Test configurable product update:
     *  - catalog_product_feed real time is enabled,
     *  - catalog_product_variants_feed real time is disabled.
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/real_time_updates 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/disabled_configurable_product_with_three_children.php
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductRealTimeConfigurableProductUpdate(): void
    {
        // Update product name
        $product = $this->productRepository->get('configurable');
        $product->setName('test');
        $this->productRepository->save($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(1, $exportQueueCollection->getSize());
        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();
        $this->assertEventExportQueueItem($exportQueueItem, $product, 'catalog_product');
    }

    /**
     * Test configurable product update:
     *  - catalog_product_feed real time is disabled,
     *  - catalog_product_variants_feed real time is enabled.
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/real_time_updates 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/disabled_configurable_product_with_three_children.php
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductVariationRealTimeConfigurableProductUpdate(): void
    {
        // Update product status
        $product = $this->productRepository->get('configurable');
        $product->setStatus(1);
        $this->productRepository->save($product);

        $firstChild = $this->productRepository->get('simple_1');
        $secondChild = $this->productRepository->get('simple_2');

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(2, $exportQueueCollection->getSize());

        /** @var ExportQueueModel[] $items */
        $items = $exportQueueCollection->getItems();
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $firstChild, false);
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $secondChild, true);
    }

    /**
     * Test configurable product update:
     *  - catalog_product_feed real time is enabled,
     *  - catalog_product_variants_feed real time is enabled.
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_feed/real_time_updates 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/catalog_id 1
     * @magentoConfigFixture bloomreach_engagement/catalog_product_variants_feed/real_time_updates 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/disabled_configurable_product_with_three_children.php
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testRealTimeConfigurableProductUpdate(): void
    {
        // Update product status
        $product = $this->productRepository->get('configurable');
        $product->setStatus(1);
        $this->productRepository->save($product);

        $firstChild = $this->productRepository->get('simple_1');
        $secondChild = $this->productRepository->get('simple_2');

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(3, $exportQueueCollection->getSize());

        /** @var ExportQueueModel[] $items */
        $items = $exportQueueCollection->getItems();
        $this->assertEventExportQueueItem(array_shift($items), $product, 'catalog_product');
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $firstChild, false);
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $secondChild, true);
    }

    /**
     * Assert ExportQueueModel item data with 'event' API type
     *
     * @param ExportQueueModel $exportQueueItem
     * @param ProductInterface $product
     * @param string $entityType
     *
     * @return void
     */
    private function assertEventExportQueueItem(
        ExportQueueModel $exportQueueItem,
        ProductInterface $product,
        string $entityType
    ): void {
        $body = $this->jsonSerializer->unserialize($exportQueueItem->getBody());
        $this->assertEquals(ExportQueueModel::STATUS_NEW, $exportQueueItem->getStatus());
        $this->assertEquals($entityType, $exportQueueItem->getEntityType());
        $this->assertEquals('event', $exportQueueItem->getApiType());
        $this->assertEquals(0, $exportQueueItem->getRetries());
        $this->assertEquals((string)$product->getId(), $body['item_id'] ?? '');
        $this->assertEquals($product->getSku(), $body['sku'] ?? '');
    }

    /**
     * Assert ExportQueueModel item data with 'partial_update' API type
     *
     * @param ExportQueueModel $exportQueueItem
     * @param ProductInterface $product
     * @param bool $productActive
     *
     * @return void
     */
    private function assertPartialUpdateExportQueueItem(
        ExportQueueModel $exportQueueItem,
        ProductInterface $product,
        bool $productActive
    ): void {
        $body = $this->jsonSerializer->unserialize($exportQueueItem->getBody());
        $expectedBody = [
            'item_id' => $product->getId(),
            'product_active' => $productActive
        ];
        $this->assertEquals(ExportQueueModel::STATUS_NEW, $exportQueueItem->getStatus());
        $this->assertEquals('catalog_product_variants', $exportQueueItem->getEntityType());
        $this->assertEquals('partial_update', $exportQueueItem->getApiType());
        $this->assertEquals(0, $exportQueueItem->getRetries());
        $this->assertEquals($expectedBody, $body);
    }

    /**
     * Test set up
     *
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $this->jsonSerializer = $objectManager->create(SerializerInterface::class);
        $this->exportQueueCollectionFactory = $objectManager->create(CollectionFactory::class);
        $this->cleanExportQueue();
    }

    /**
     * Clean export queue after each test
     *
     * @return void
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanExportQueue();
    }

    /**
     * Clean export queue
     *
     * @return void
     * @throws Exception
     */
    private function cleanExportQueue(): void
    {
        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $exportQueueResourceModel = $exportQueueCollection->getResource();
        /** @var ExportQueueModel $exportQueueItem */
        foreach ($exportQueueCollection as $exportQueueItem) {
            $exportQueueResourceModel->delete($exportQueueItem);
        }
    }
}
