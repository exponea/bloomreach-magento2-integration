<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Test\Integration\Model\Export;

use Bloomreach\EngagementConnector\Api\DeleteInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Api\SaveInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\DataProvider\EntityType;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResource;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue\Collection as ExportQueueCollection;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Contains general logic for processors tests
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractProcessorTestCase extends TestCase
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
     * @var ExportQueueResource
     */
    protected $exportQueueResource;

    /**
     * @var DeleteInitialExportStatusInterface
     */
    protected $deleteInitialExportStatus;

    /**
     * @var EntityType
     */
    protected $entityType;

    /**
     * @param string $entityType
     * @param int $status
     * @param bool $isLocked
     * @param int $totalItems
     *
     * @return void
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    protected function createInitialExportStatus(
        string $entityType,
        int $status,
        bool $isLocked = false,
        int $totalItems = 0
    ): void {
        $initialExportStatus = $this->initialExportStatusGetter->execute($entityType);
        $initialExportStatus->setStatus($status);
        $initialExportStatus->setIsLocked($isLocked);
        $initialExportStatus->setTotalItems($totalItems);
        $this->saveInitialExportStatus->execute($initialExportStatus);
    }

    /**
     * Get export queue collection
     *
     * @return ExportQueueCollection
     */
    protected function getExportQueueCollection(): ExportQueueCollection
    {
        return $this->objectManager->create(ExportQueueCollection::class);
    }

    /**
     * Test set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureObjectManager();
        $this->initialExportStatusGetter = $this->objectManager->get(InitialExportStatusGetter::class);
        $this->saveInitialExportStatus = $this->objectManager->get(SaveInitialExportStatusInterface::class);
        $this->exportQueueResource = $this->objectManager->get(ExportQueueResource::class);
        $this->deleteInitialExportStatus = $this->objectManager->get(DeleteInitialExportStatusInterface::class);
        $this->entityType = $this->objectManager->get(EntityType::class);
    }

    /**
     * Configure Object Manager
     *
     * @return void
     */
    protected function configureObjectManager(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Delete data after test
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUpInitialImportStatus();
        $this->cleanUpExportQueue();
    }

    /**
     * Delete data after test
     *
     * @return void
     */
    private function cleanUpInitialImportStatus(): void
    {
        parent::tearDown();

        try {
            foreach ($this->entityType->getAllTypes() as $entityType) {
                $this->deleteInitialExportStatus->execute($entityType);
            }
        } catch (\Exception $e) {
            //Nothing to do
        }
    }

    /**
     * Clean up export queue
     *
     * @return void
     */
    private function cleanUpExportQueue(): void
    {
        try {
            foreach ($this->getExportQueueCollection()->getItems() as $exportQueue) {
                $this->exportQueueResource->delete($exportQueue);
            }
        } catch (\Exception $e) {
            //Nothing to do
        }
    }
}
