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
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
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
     * Customer update event test
     *
     * @magentoAppArea adminhtml
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/customer.php
     *
     * @return void
     */
    public function testCustomerUpdate(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $customer->setFirstname('Veronica');
        $customer->setLastname('Costello');
        $this->customerRepository->save($customer);

        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $exportQueueCollection->addFieldToFilter(ExportQueueModel::ENTITY_TYPE, 'customer');
        $exportQueueCollection->addFieldToFilter(ExportQueueModel::API_TYPE, 'event');

        /** @var ExportQueueModel $exportQueueItem */
        $exportQueueItem = $exportQueueCollection->getLastItem();
        $body = $this->jsonSerializer->unserialize($exportQueueItem->getBody());

        $this->assertEquals(ExportQueueModel::STATUS_NEW, (int) $exportQueueItem->getStatus());
        $this->assertEquals(0, (int) $exportQueueItem->getRetries());
        $this->assertEquals('customer@example.com', $body['email']);
        $this->assertEquals('Veronica', $body['first_name']);
        $this->assertEquals('Costello', $body['last_name']);
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

    /**
     * Clean export queue
     *
     * @return void
     * @throws Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        /** @var Collection $exportQueueCollection */
        $exportQueueCollection = $this->exportQueueCollectionFactory->create();
        $exportQueueResourceModel = $exportQueueCollection->getResource();
        /** @var ExportQueueModel $exportQueueItem */
        foreach ($exportQueueCollection as $exportQueueItem) {
            $exportQueueResourceModel->delete($exportQueueItem);
        }
    }
}
