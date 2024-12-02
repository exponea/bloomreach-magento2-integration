<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Event\Order;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Order;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\OrderItem;
use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection as ExportQueueCollection;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Covers place order event
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PlaceOrderTest extends TestCase
{
    private const EXPORT_QUEUE_API_TYPE = 'event';

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ExportQueueResource
     */
    private $exportQueueResource;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Covers case when guest customer places the order and all settings are enabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testGuestCustomerPlaceOrder(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(1, 1, 1);
    }

    /**
     * Covers case when registered customer places the order and all settings are enabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/customer.php
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testCustomerPlaceOrder(): void
    {
        $this->deleteCustomerFromExportQueue(); //Delete customer events from export queue
        $this->placeCustomerOrder();
        $this->assertPlaceOrder(0, 1, 1);
    }

    /**
     * Covers case when guest customer places the order and feature is disabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 0
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testExtensionDisabled(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(0, 0, 0);
    }

    /**
     * Covers case when guest customer places the order and purchase feed is disabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 0
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testPurchaseFeedDisabled(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(1, 0, 1);
    }

    /**
     * Covers case when guest customer places the order and purchase item feed is disabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 0
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testPurchaseItemFeedDisabled(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(1, 1, 0);
    }

    /**
     * Covers case when guest customer places the order and purchase feed realtime update is disabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 0
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testPurchaseFeedRealTimeUpdateDisabled(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(1, 0, 1);
    }

    /**
     * Covers case when guest customer places the order and purchase item feed realtime update is disabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 0
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testPurchaseItemFeedRealTimeUpdateDisabled(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(1, 1, 0);
    }

    /**
     * Covers case when guest customer places the order and customer feed is disabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 0
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testCustomerFeedDisabled(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(0, 1, 1);
    }

    /**
     * Covers case when guest customer places the order and customer feed realtime update is disabled
     *
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/real_time_updates 0
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_feed/real_time_updates 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/purchase_item_feed/real_time_updates 1
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/product_simple.php
     *
     * @return void
     */
    public function testCustomerRealTimeUpdateDisabled(): void
    {
        $this->placeGuestOrder();
        $this->assertPlaceOrder(0, 1, 1);
    }

    /**
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->exportQueueResource = $this->objectManager->get(ExportQueueResource::class);
        $this->cartManagement = $this->objectManager->get(CartManagementInterface::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->quoteRepository = $this->objectManager->get(QuoteRepository::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
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
            /** @var ExportQueueInterface $exportQueue */
            foreach ($this->getExportQueueCollection()->getItems() as $exportQueue) {
                $this->exportQueueResource->delete($exportQueue);
            }
        } catch (Exception $e) {
            //Nothing to do
        }
    }

    /**
     * Place customer order
     *
     * @return void
     * @throws LocalizedException
     */
    private function placeCustomerOrder(): void
    {
        $this->placeOrder(
            (int) $this->cartManagement->createEmptyCartForCustomer(
                $this->customerRepository->get('customer@example.com')->getId()
            )
        );
    }

    /**
     * Place guest customer order
     *
     * @return void
     * @throws LocalizedException
     */
    private function placeGuestOrder(): void
    {
        $this->placeOrder(
            (int) $this->cartManagement->createEmptyCart()
        );
    }

    /**
     * Place an order
     *
     * @param int $quoteId
     *
     * @return void
     * @throws LocalizedException
     */
    private function placeOrder(int $quoteId): void
    {
        $product = $this->productRepository->get('simple');
        $product->setData('salable', true);
        $quote = $this->quoteRepository->getActive($quoteId);

        if ($quote->getCustomerIsGuest()) {
            $this->addGuestCustomerData($quote);
        }

        $quote->addProduct($product);
        $this->quoteRepository->save($quote);
        $this->prepareQuote($quote);
        $this->cartManagement->placeOrder($quote->getId());
    }

    /**
     * Assert place order
     *
     * @param int $customerExpected
     * @param int $purchaseExpected
     * @param int $purchaseItemsExpected
     *
     * @return void
     */
    private function assertPlaceOrder(int $customerExpected, int $purchaseExpected, int $purchaseItemsExpected): void
    {
        $this->assertEquals(
            $customerExpected,
            $this->getExportQueueCollection()
                ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, Customer::ENTITY_TYPE)
                ->addFieldToFilter(ExportQueueModel::API_TYPE, self::EXPORT_QUEUE_API_TYPE)
                ->getSize()
        );
        $this->assertEquals(
            $purchaseExpected,
            $this->getExportQueueCollection()
                ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, Order::ENTITY_TYPE)
                ->addFieldToFilter(ExportQueueModel::API_TYPE, self::EXPORT_QUEUE_API_TYPE)
                ->getSize()
        );
        $this->assertEquals(
            $purchaseItemsExpected,
            $this->getExportQueueCollection()
                ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, OrderItem::ENTITY_TYPE)
                ->addFieldToFilter(ExportQueueModel::API_TYPE, self::EXPORT_QUEUE_API_TYPE)
                ->getSize()
        );
    }

    /**
     * Get export queue collection
     *
     * @return ExportQueueCollection
     */
    private function getExportQueueCollection(): ExportQueueCollection
    {
        return $this->objectManager->create(ExportQueueCollection::class);
    }

    /**
     * Select shipping and select payment
     *
     * @param CartInterface $quote
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function prepareQuote(CartInterface $quote): void
    {
        $quote->getBillingAddress()->addData($this->getCustomerAddress());
        $quote->getShippingAddress()->addData($this->getCustomerAddress());
        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate');
        $quote->setPaymentMethod('checkmo');
        $quote->setInventoryProcessed(false);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);
        $quote->getPayment()->importData(['method' => 'checkmo']);
        $this->quoteRepository->save($quote);
    }

    /**
     * Returns customer address for quote
     *
     * @return array
     */
    private function getCustomerAddress(): array
    {
        return [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => 'xxxxxx',
            'city' => 'xxxxxxx',
            'country_id' => 'US',
            'region' => 'Texas',
            'region_id' => '57',
            'postcode' => '85001',
            'telephone' => '52556542',
            'fax' => '3242322556',
            'save_in_address_book' => 0
        ];
    }

    /**
     * Add guest customer data
     *
     * @param CartInterface $quote
     *
     * @return void
     */
    private function addGuestCustomerData(CartInterface $quote): void
    {
        $quote->setCustomerEmail('john.doe@example.com');
        $quote->setCustomerFirstname('John');
        $quote->setCustomerLastname('Doe');
    }

    /**
     * Delete customer from Export queue
     *
     * @return void
     * @throws Exception
     */
    private function deleteCustomerFromExportQueue(): void
    {
        $collection = $this->getExportQueueCollection()
            ->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, Customer::ENTITY_TYPE);

        foreach ($collection as $exportQueue) {
            $this->exportQueueResource->delete($exportQueue);
        }
    }
}
