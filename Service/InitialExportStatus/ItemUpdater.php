<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Service\InitialExportStatus;

use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\InitialExportStatus\Source\StatusSource;

/**
 * The class is responsible for updating item depending on progress status
 */
class ItemUpdater
{
    private const NOT_STARTED_IMPORT_STATUSES = [
        StatusSource::DISABLED,
        StatusSource::NOT_READY,
        StatusSource::READY,
    ];

    /**
     * @var ProgressStatusResolver
     */
    private $progressStatusResolver;

    /**
     * @param ProgressStatusResolver $progressStatusResolver
     */
    public function __construct(ProgressStatusResolver $progressStatusResolver)
    {
        $this->progressStatusResolver = $progressStatusResolver;
    }

    /**
     * Update item depending on progress status
     *
     * @param InitialExportStatusInterface $initialExportStatus
     *
     * @return InitialExportStatusInterface
     */
    public function execute(InitialExportStatusInterface $initialExportStatus): InitialExportStatusInterface
    {
        $status = $this->progressStatusResolver->execute($initialExportStatus);
        $initialExportStatus->setStatus($status);

        if (in_array($status, self::NOT_STARTED_IMPORT_STATUSES)) {
            $initialExportStatus->setTotalItems(0);
            $initialExportStatus->setTotalErrorItems(0);
            $initialExportStatus->setTotalExported(0);
            $initialExportStatus->setErrors('');
            $initialExportStatus->setStartedAt(null);
            $initialExportStatus->setFinishedAt(null);
        }

        return $initialExportStatus;
    }
}
