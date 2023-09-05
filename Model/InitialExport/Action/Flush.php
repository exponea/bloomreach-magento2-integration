<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action;

use Bloomreach\EngagementConnector\Api\DeleteInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Flush\DeleteCatalog;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Flush\DeleteImport;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Validator\ActionExecute as ActionExecuteValidator;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResource;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Magento\Framework\App\Cache\TypeListInterface as CacheTypeList;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class is responsible for flushing the initial import
 *
 * Deletes Import and Catalog for entity type on the Bloomreach side
 *
 * Deletes all data for entity type on the Magento side
 */
class Flush
{
    /**
     * @var ExportQueueResource
     */
    private $exportQueueResource;

    /**
     * @var DeleteInitialExportStatusInterface
     */
    private $deleteInitialExportStatus;

    /**
     * @var CacheTypeList
     */
    private $cacheTypeList;

    /**
     * @var DeleteCatalog
     */
    private $deleteCatalog;

    /**
     * @var DeleteImport
     */
    private $deleteImport;

    /**
     * @var InitialExportStatusGetter
     */
    private $initialExportStatusGetter;

    /**
     * @var ActionExecuteValidator
     */
    private $actionExecuteValidator;

    /**
     * @param ExportQueueResource $exportQueueResource
     * @param DeleteInitialExportStatusInterface $deleteInitialExportStatus
     * @param CacheTypeList $cacheTypeList
     * @param DeleteCatalog $deleteCatalog
     * @param DeleteImport $deleteImport
     * @param InitialExportStatusGetter $initialExportStatusGetter
     * @param ActionExecuteValidator $actionExecuteValidator
     */
    public function __construct(
        ExportQueueResource $exportQueueResource,
        DeleteInitialExportStatusInterface $deleteInitialExportStatus,
        CacheTypeList $cacheTypeList,
        DeleteCatalog $deleteCatalog,
        DeleteImport $deleteImport,
        InitialExportStatusGetter $initialExportStatusGetter,
        ActionExecuteValidator $actionExecuteValidator
    ) {
        $this->exportQueueResource = $exportQueueResource;
        $this->deleteInitialExportStatus = $deleteInitialExportStatus;
        $this->cacheTypeList = $cacheTypeList;
        $this->deleteCatalog = $deleteCatalog;
        $this->deleteImport = $deleteImport;
        $this->initialExportStatusGetter = $initialExportStatusGetter;
        $this->actionExecuteValidator = $actionExecuteValidator;
    }

    /**
     * Stops the initial import
     *
     * @param string $entityType
     * @param string $scope
     * @param int $scopeId
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function execute(
        string $entityType,
        string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        int $scopeId = 0
    ): void {
        $this->actionExecuteValidator->validate(
            $this->initialExportStatusGetter->execute($entityType),
            ActionExecuteValidator::FLUSH_ACTION
        );
        $this->deleteCatalog->execute($entityType, $scope, $scopeId);
        $this->deleteImport->execute($entityType, $scope, $scopeId);
        $this->deleteInitialExportStatus->execute($entityType);
        $this->exportQueueResource->deleteByEntityType($entityType);
        $this->cacheTypeList->cleanType('config');
    }
}
