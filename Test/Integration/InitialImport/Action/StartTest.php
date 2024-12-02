<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\InitialImport\Action;

use Bloomreach\EngagementConnector\Model\DataMapping\DataMapper\Product\DefaultType;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Start;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;

/**
 * Cover enable import action with integration tests
 */
class StartTest extends AbstractActionTestCase
{
    /**
     * @var Start
     */
    private $startAction;

    /**
     * Test success case
     *
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/enabled 1
     * @magentoConfigFixture default/bloomreach_engagement/catalog_product_feed/import_id test_import_id
     *
     * @return void
     */
    public function testSuccess()
    {
        $this->executeAction(DefaultType::ENTITY_TYPE);
        $this->assertEquals(
            StatusSource::SCHEDULED,
            $this->initialExportStatusGetter->execute(DefaultType::ENTITY_TYPE)->getStatus()
        );
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
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->startAction = $this->objectManager->get(Start::class);
    }

    /**
     * Execute action
     *
     * @param string $entityType
     *
     * @return void
     */
    protected function executeAction(string $entityType): void
    {
        $this->startAction->execute($entityType);
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
        return 'Unable to start import. Current import status: ' . $statusLabel;
    }
}
