<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\InitialImport\Action;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Customer;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\ProductVariantsType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Configure;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Enable as EnableInitialExport;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Service\Integration\Client\RequestSender;
use Bloomreach\EngagementConnector\Service\Integration\CreateCatalog;
use Bloomreach\EngagementConnector\Service\Integration\CreateImport;
use Bloomreach\EngagementConnector\Service\Integration\Response\ResponseValidator;
use Bloomreach\EngagementConnector\System\CatalogIdResolver;
use Bloomreach\EngagementConnector\System\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Cover configure import action with integration tests
 *
 * @magentoAppArea adminhtml
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigureTest extends AbstractActionTestCase
{
    private const CATALOG_TYPES = [
        DefaultType::ENTITY_TYPE,
        ProductVariantsType::ENTITY_TYPE
    ];

    /**
     * @var CatalogIdResolver
     */
    private $catalogIdResolver;

    /**
     * @var Configure
     */
    private $configureInitialExport;

    /**
     * @var EnableInitialExport
     */
    private $enableInitialExport;

    /**
     * Test success action
     *
     * @param string $entityType
     *
     * @return void
     *
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/customer.php
     * @magentoDataFixture Bloomreach_EngagementConnector::Test/Integration/_files/order.php
     * @dataProvider entityDataProvider
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testSuccess(string $entityType): void
    {
        $this->enableInitialExport->execute($entityType);
        $this->scopeConfig->clean();
        $this->executeAction($entityType);
        $this->scopeConfig->clean();
        $this->assertEquals(
            StatusSource::READY,
            $this->initialExportStatusGetter->execute($entityType)->getStatus()
        );

        if (!in_array($entityType, self::CATALOG_TYPES, true)) {
            $this->expectException(LocalizedException::class);
        }

        $this->assertNotEmpty($this->catalogIdResolver->getCatalogId($entityType));
    }

    /**
     * Test no items exception
     *
     * @return void
     *
     * @magentoConfigFixture default/bloomreach_engagement/customer_feed/enabled 1
     * @throws LocalizedException
     */
    public function testNoItemsException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage(sprintf('You have no items in Customer Feed. Create one and try again'));
        $this->executeAction(Customer::ENTITY_TYPE);
    }

    /**
     * Test validation exception when import status is "Disabled"
     *
     * @return void
     */
    public function testValidationExceptionWithDisabledStatus(): void
    {
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, 'Disabled');
    }

    /**
     * Test validation exception when import status is "Ready"
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     *
     * @return void
     */
    public function testValidationExceptionWithReadyStatus(): void
    {
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, 'Ready');
    }

    /**
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->enableInitialExport = $this->objectManager->get(EnableInitialExport::class);
        $this->configureInitialExport = $this->objectManager->get(Configure::class);
        $this->catalogIdResolver = $this->objectManager->get(CatalogIdResolver::class);
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
        $this->objectManager->addSharedInstance($createImportMock, CreateImport::class);
        $this->objectManager->addSharedInstance($createCatalogMock, CreateCatalog::class);
    }

    /**
     * Execute configure action
     *
     * @param string $entityType
     *
     * @return void
     * @throws LocalizedException
     */
    protected function executeAction(string $entityType): void
    {
        $this->configureInitialExport->execute($entityType);
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
        return 'Import is already configured. Current import status: ' . $statusLabel;
    }
}
