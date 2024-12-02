<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Event\Customer;

use Bloomreach\EngagementConnector\Model\ExportQueueModel;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Customer update event test
 */
class CustomerUpdateTest extends TestCase
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var CollectionFactory
     */
    private $exportQueueCollectionFactory;

    /**
     * Customer update event test (real time update is disabled)
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/customer_feed/real_time_updates 0
     * @magentoAppArea adminhtml
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/customer.php
     *
     * @return void
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testCustomerUpdate(): void
    {
        $this->updateCustomerData();
        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $this->assertEquals(0, $exportQueueCollection->getSize());
    }

    /**
     * Real time customer update event test
     *
     * @magentoAppIsolation enabled
     * @magentoConfigFixture bloomreach_engagement/general/enable 1
     * @magentoConfigFixture bloomreach_engagement/customer_feed/enabled 1
     * @magentoConfigFixture bloomreach_engagement/customer_feed/real_time_updates 1
     * @magentoAppArea adminhtml
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/customer.php
     *
     * @return void
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testRealTimeCustomerUpdate(): void
    {
        $this->updateCustomerData();

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $exportQueueCollection->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, 'customer');
        $exportQueueCollection->addFieldToFilter(ExportQueueModel::API_TYPE, 'event');

        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();

        $this->assertEquals(true, (bool) $exportQueueItem->getId());
        $body = $this->jsonSerializer->unserialize($exportQueueItem->getBody());

        $this->assertEquals(ExportQueueModel::STATUS_NEW, $exportQueueItem->getStatus());
        $this->assertEquals(0, $exportQueueItem->getRetries());
        $this->assertEquals('customer@example.com', $body['email'] ?? '');
        $this->assertEquals('Veronica', $body['first_name'] ?? '');
        $this->assertEquals('Costello', $body['last_name'] ?? '');
    }

    /**
     * Update customer data to trigger related logic
     *
     * @return void
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputMismatchException
     */
    private function updateCustomerData(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $customer->setFirstname('Veronica');
        $customer->setLastname('Costello');
        $this->customerRepository->save($customer);
    }

    /**
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
        $this->jsonSerializer = $objectManager->create(SerializerInterface::class);
        $this->exportQueueCollectionFactory = $objectManager->create(CollectionFactory::class);
    }
}
