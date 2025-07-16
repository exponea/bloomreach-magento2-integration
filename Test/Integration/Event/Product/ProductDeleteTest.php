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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Product delete event test
 *
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class ProductDeleteTest extends TestCase
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
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $objectManager->create(ProductRepositoryInterface::class);
        $this->jsonSerializer = $objectManager->create(SerializerInterface::class);
        $this->exportQueueCollectionFactory = $objectManager->create(CollectionFactory::class);
    }

    /**
     * Test simple product delete (real time related configs are disabled)
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testSimpleProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('simple');
        $this->productRepository->delete($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(0, $exportQueueCollection->getSize());
    }

    /**
     * Test simple product delete:
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
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductRealTimeSimpleProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('simple');
        $this->productRepository->delete($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(1, $exportQueueCollection->getSize());
        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();
        $this->assertDeleteExportQueueItem($exportQueueItem, $product, 'catalog_product');
    }

    /**
     * Test simple product delete:
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
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductVariationRealTimeSimpleProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('simple');
        $this->productRepository->delete($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(1, $exportQueueCollection->getSize());

        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();
        $this->assertDeleteExportQueueItem($exportQueueItem, $product, 'catalog_product_variants');
    }

    /**
     * Test simple product delete:
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
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testRealTimeSimpleProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('simple');
        $this->productRepository->delete($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(2, $exportQueueCollection->getSize());

        /** @var ExportQueueModel[] $items */
        $items = $exportQueueCollection->getItems();
        $this->assertDeleteExportQueueItem(array_shift($items), $product, 'catalog_product_variants');
        $this->assertDeleteExportQueueItem(array_shift($items), $product, 'catalog_product');
    }

    /**
     * Test configurable product delete (real time related configs are disabled)
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/disabled_configurable_product_with_three_children.php
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testConfigurableProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('configurable');
        $this->productRepository->delete($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(0, $exportQueueCollection->getSize());
    }

    /**
     * Test configurable product delete:
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
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductRealTimeConfigurableProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('configurable');
        $this->productRepository->delete($product);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(1, $exportQueueCollection->getSize());
        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();
        $this->assertDeleteExportQueueItem($exportQueueItem, $product, 'catalog_product');
    }

    /**
     * Test configurable product delete:
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
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testCatalogProductVariationRealTimeConfigurableProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('configurable');
        $this->productRepository->delete($product);

        $firstChild = $this->productRepository->get('simple_1');
        $secondChild = $this->productRepository->get('simple_2');

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(2, $exportQueueCollection->getSize());

        /** @var ExportQueueModel[] $items */
        $items = $exportQueueCollection->getItems();
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $firstChild);
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $secondChild);
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
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function testRealTimeConfigurableProductDelete(): void
    {
        // Delete product
        $product = $this->productRepository->get('configurable');
        $this->productRepository->delete($product);

        $firstChild = $this->productRepository->get('simple_1');
        $secondChild = $this->productRepository->get('simple_2');

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(3, $exportQueueCollection->getSize());

        /** @var ExportQueueModel[] $items */
        $items = $exportQueueCollection->getItems();
        $this->assertDeleteExportQueueItem(array_shift($items), $product, 'catalog_product');
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $firstChild);
        $this->assertPartialUpdateExportQueueItem(array_shift($items), $secondChild);
    }

    /**
     * Assert ExportQueueModel item data with 'delete' API type
     *
     * @param ExportQueueModel $exportQueueItem
     * @param ProductInterface $product
     * @param string $entityType
     *
     * @return void
     */
    private function assertDeleteExportQueueItem(
        ExportQueueModel $exportQueueItem,
        ProductInterface $product,
        string $entityType
    ): void {
        $this->assertEquals(ExportQueueModel::STATUS_NEW, $exportQueueItem->getStatus());
        $this->assertEquals($entityType, $exportQueueItem->getEntityType());
        $this->assertEquals('delete', $exportQueueItem->getApiType());
        $this->assertEquals(0, $exportQueueItem->getRetries());
        $this->assertEquals($exportQueueItem->getBody(), (string)$product->getId());
    }

    /**
     * Assert ExportQueueModel item data with 'partial_update' API type
     *
     * @param ExportQueueModel $exportQueueItem
     * @param ProductInterface $product
     *
     * @return void
     */
    private function assertPartialUpdateExportQueueItem(
        ExportQueueModel $exportQueueItem,
        ProductInterface $product
    ): void {
        $body = $this->jsonSerializer->unserialize($exportQueueItem->getBody());
        $expectedBody = [
            'item_id' => $product->getId(),
            'product_active' => false
        ];
        $this->assertEquals(ExportQueueModel::STATUS_NEW, $exportQueueItem->getStatus());
        $this->assertEquals('catalog_product_variants', $exportQueueItem->getEntityType());
        $this->assertEquals('partial_update', $exportQueueItem->getApiType());
        $this->assertEquals(0, $exportQueueItem->getRetries());
        $this->assertEquals($expectedBody, $body);
    }
}
