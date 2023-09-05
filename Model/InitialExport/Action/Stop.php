<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action;

use Bloomreach\EngagementConnector\Api\DeleteInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Validator\ActionExecute as ActionExecuteValidator;
use Bloomreach\EngagementConnector\Model\ResourceModel\ExportQueue as ExportQueueResource;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class is responsible for stopping the initial import
 *
 * Deletes all data for entity type on the Magento side
 */
class Stop
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
     * @param InitialExportStatusGetter $initialExportStatusGetter
     * @param ActionExecuteValidator $actionExecuteValidator
     */
    public function __construct(
        ExportQueueResource $exportQueueResource,
        DeleteInitialExportStatusInterface $deleteInitialExportStatus,
        InitialExportStatusGetter $initialExportStatusGetter,
        ActionExecuteValidator $actionExecuteValidator
    ) {
        $this->exportQueueResource = $exportQueueResource;
        $this->deleteInitialExportStatus = $deleteInitialExportStatus;
        $this->initialExportStatusGetter = $initialExportStatusGetter;
        $this->actionExecuteValidator = $actionExecuteValidator;
    }

    /**
     * Stops the initial import
     *
     * @param string $entityType
     *
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function execute(string $entityType): void
    {
        $this->actionExecuteValidator->validate(
            $this->initialExportStatusGetter->execute($entityType),
            ActionExecuteValidator::STOP_ACTION
        );
        $this->deleteInitialExportStatus->execute($entityType);
        $this->exportQueueResource->deleteByEntityType($entityType);
    }
}
