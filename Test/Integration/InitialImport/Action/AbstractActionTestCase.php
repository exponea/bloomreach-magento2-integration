<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\InitialImport\Action;

use Bloomreach\EngagementConnector\Api\DeleteInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\SaveInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Order;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\OrderItem;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\Export\Condition\IsEntityTypeFeedEnabled;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Contains general logic for action tests
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractActionTestCase extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var InitialExportStatusGetter
     */
    protected $initialExportStatusGetter;

    /**
     * @var SaveInitialExportStatusInterface
     */
    protected $saveInitialExportStatus;

    /**
     * @var DeleteInitialExportStatusInterface
     */
    protected $deleteInitialExportStatus;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var EntityType
     */
    protected $entityType;

    /**
     * Test invalid entity type exception
     *
     * @return void
     */
    public function testInvalidEntityTypeException(): void
    {
        $entityType = 'test-type';
        $this->expectException(NoSuchEntityException::class);
        $this->expectExceptionMessage('Entity Type with code ' . $entityType . ' does not exists');
        $this->executeAction($entityType);
    }

    /**
     * Test validation exception
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     * @dataProvider initialImportStatusDataProvider
     *
     * @param string $statusLabel
     * @param int $status
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidationException(string $statusLabel, int $status): void
    {
        $initialExportStatus = $this->initialExportStatusGetter->execute(DefaultType::ENTITY_TYPE);
        $initialExportStatus->setStatus($status);
        $this->saveInitialExportStatus->execute($initialExportStatus);
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, $statusLabel);
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
                'statusLabel' => 'Scheduled',
                'status' => StatusSource::SCHEDULED
            ],
            [
                'statusLabel' => 'Processing',
                'status' => StatusSource::PROCESSING
            ],
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
     * Data provider
     *
     * @return array[]
     */
    public function entityDataProvider(): array
    {
        return [
            [
                'entityType' => DefaultType::ENTITY_TYPE,
                'label' => 'Product Feed'
            ],
            [
                'entityType' => ProductVariantsType::ENTITY_TYPE,
                'label' => 'Product Variants Feed'
            ],
            [
                'entityType' => Customer::ENTITY_TYPE,
                'label' => 'Customer Feed'
            ],
            [
                'entityType' => Order::ENTITY_TYPE,
                'label' => 'Purchase Feed'
            ],
            [
                'entityType' => OrderItem::ENTITY_TYPE,
                'label' => 'Purchase Items Feed'
            ]
        ];
    }

    /**
     * Assert validation exception
     *
     * @param string $entityType
     * @param string $statusLabel
     *
     * @return void
     */
    protected function validationExceptionTest(string $entityType, string $statusLabel): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage($this->getValidationExceptionMessage($statusLabel));
        $this->executeAction($entityType);
    }

    /**
     * Execute action
     *
     * @param string $entityType
     *
     * @return void
     */
    abstract protected function executeAction(string $entityType): void;

    /**
     * Get validation exception message
     *
     * @param string $statusLabel
     *
     * @return string
     */
    abstract protected function getValidationExceptionMessage(string $statusLabel): string;

    /**
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->configureObjectManager();
        $this->initialExportStatusGetter = $this->objectManager->get(InitialExportStatusGetter::class);
        $this->saveInitialExportStatus = $this->objectManager->get(SaveInitialExportStatusInterface::class);
        $this->deleteInitialExportStatus = $this->objectManager->get(DeleteInitialExportStatusInterface::class);
        $this->scopeConfig = $this->objectManager->get(ScopeConfigInterface::class);
        $this->entityType = $this->objectManager->get(EntityType::class);
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
            foreach ($this->entityType->getAllTypes() as $entityType) {
                $this->deleteInitialExportStatus->execute($entityType);
            }
        } catch (Exception $e) {
            //Nothing to do
        }
    }

    /**
     * Configure object Manager
     *
     * @return void
     */
    protected function configureObjectManager(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->configure(
            [
                IsEntityTypeFeedEnabled::class => [
                    'arguments' => [
                        'useCache' => false
                    ]
                ]
            ]
        );
    }
}
