<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\InitialImport\Action;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Enable as EnableInitialExport;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Cover enable import action with integration tests
 */
class EnableTest extends AbstractActionTestCase
{
    /**
     * @var EnableInitialExport
     */
    private $enableInitialExport;

    /**
     * Test success case
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testSuccess()
    {
        $this->executeAction(DefaultType::ENTITY_TYPE);
        $this->scopeConfig->clean();
        $this->assertEquals(
            StatusSource::NOT_READY,
            $this->initialExportStatusGetter->execute(DefaultType::ENTITY_TYPE)->getStatus()
        );
    }

    /**
     *  Test validation exception when import status is "Not Ready"
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     *
     * @return void
     * @throws LocalizedException
     */
    public function testValidationExceptionWithNotReadyStatus(): void
    {
        $this->validationExceptionTest(DefaultType::ENTITY_TYPE, 'Not Ready');
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
        $this->enableInitialExport->execute($entityType);
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
        return 'Import is already enabled. Current import status: ' . $statusLabel;
    }
}
