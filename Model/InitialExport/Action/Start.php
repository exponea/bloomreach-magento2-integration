<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterfaceFactory;
use Bloomreach\EngagementConnector\Api\SaveInitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExport\Action\Validator\ActionExecute as ActionExecuteValidator;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Bloomreach\EngagementConnector\Service\InitialExportStatus\ItemGetter as InitialExportStatusGetter;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class is responsible for starting the initial import
 */
class Start
{
    /**
     * @var SaveInitialExportStatusInterface
     */
    private $saveInitialExportStatus;

    /**
     * @var InitialExportStatusGetter
     */
    private $initialExportStatusGetter;

    /**
     * @var ActionExecuteValidator
     */
    private $actionExecuteValidator;

    /**
     * @param SaveInitialExportStatusInterface $saveInitialExportStatus
     * @param InitialExportStatusGetter $initialExportStatusGetter
     * @param ActionExecuteValidator $actionExecuteValidator
     */
    public function __construct(
        SaveInitialExportStatusInterface $saveInitialExportStatus,
        InitialExportStatusGetter $initialExportStatusGetter,
        ActionExecuteValidator $actionExecuteValidator
    ) {
        $this->saveInitialExportStatus = $saveInitialExportStatus;
        $this->initialExportStatusGetter = $initialExportStatusGetter;
        $this->actionExecuteValidator = $actionExecuteValidator;
    }

    /**
     * Starts the initial import
     *
     * @param string $entityType
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(string $entityType): void
    {
        $initialExportStatus = $this->initialExportStatusGetter->execute($entityType);
        $this->actionExecuteValidator->validate($initialExportStatus, ActionExecuteValidator::START_ACTION);
        $initialExportStatus->setEntityType($entityType);
        $initialExportStatus->setStatus(StatusSource::SCHEDULED);
        $this->saveInitialExportStatus->execute($initialExportStatus);
    }
}
