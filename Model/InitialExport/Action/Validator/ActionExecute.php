<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\InitialExport\Action\Validator;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;
use Magento\Framework\Exception\LocalizedException;

/**
 * The class is responsible for checking if execute action is allowed
 */
class ActionExecute
{
    public const ENABLE_ACTION = 'enable';

    public const CONFIGURE_ACTION = 'configure';

    public const START_ACTION = 'start';

    public const STOP_ACTION = 'stop';

    public const FLUSH_ACTION = 'flush';

    private const IN_PROGRESS_STATUSES = [
        StatusSource::SCHEDULED,
        StatusSource::PROCESSING
    ];

    private const CAN_FLUSH_STATUSES = [
        StatusSource::READY,
        StatusSource::SCHEDULED,
        StatusSource::PROCESSING,
        StatusSource::ERROR,
        StatusSource::SUCCESS
    ];

    /**
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @param StatusSource $statusSource
     */
    public function __construct(StatusSource $statusSource)
    {
        $this->statusSource = $statusSource;
    }

    /**
     * The class is responsible for checking if execute action is allowed
     *
     * @param InitialExportStatusInterface $initialExportStatus
     * @param string $actionName
     *
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate(InitialExportStatusInterface $initialExportStatus, string $actionName): void
    {
        $message = '';

        if ($actionName === self::ENABLE_ACTION && $initialExportStatus->getStatus() !== StatusSource::DISABLED) {
            $message =__(
                'Import is already enabled. Current import status: %import_status',
                [
                    'import_status' => $this->statusSource->getStatusLabel($initialExportStatus->getStatus())
                ]
            );
        }

        if ($actionName === self::CONFIGURE_ACTION && $initialExportStatus->getStatus() !== StatusSource::NOT_READY) {
            $message =__(
                'Import is already configured. Current import status: %import_status',
                [
                    'import_status' => $this->statusSource->getStatusLabel($initialExportStatus->getStatus())
                ]
            );
        }

        if ($actionName === self::START_ACTION && $initialExportStatus->getStatus() !== StatusSource::READY) {
            $message = __(
                'Unable to start import. Current import status: %import_status',
                [
                    'import_status' => $this->statusSource->getStatusLabel($initialExportStatus->getStatus())
                ]
            );
        }

        if ($actionName === self::STOP_ACTION
            && !in_array($initialExportStatus->getStatus(), self::IN_PROGRESS_STATUSES)
        ) {
            $message = __(
                'Unable to stop import. Current import status: %import_status',
                [
                    'import_status' => $this->statusSource->getStatusLabel($initialExportStatus->getStatus())
                ]
            );
        }

        if ($actionName === self::FLUSH_ACTION
            && !in_array($initialExportStatus->getStatus(), self::CAN_FLUSH_STATUSES)
        ) {
            $message = __(
                'Unable to flush import. Current import status: %import_status',
                [
                    'import_status' => $this->statusSource->getStatusLabel($initialExportStatus->getStatus())
                ]
            );
        }

        if ($message) {
            throw new LocalizedException($message);
        }
    }
}
