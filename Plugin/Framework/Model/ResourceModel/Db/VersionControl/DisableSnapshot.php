<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Plugin\Framework\Model\ResourceModel\Db\VersionControl;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Bloomreach\EngagementConnector\Api\Data\InitialExportStatusInterface;
use Bloomreach\EngagementConnector\Model\DataProvider\DB\SnapshotSettings;
use Magento\Framework\DataObject;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot as Subject;

/**
 * This plugin is responsible for disabling db snapshot
 */
class DisableSnapshot
{
    /**
     * @var SnapshotSettings
     */
    private $snapshotSettings;

    /**
     * @param SnapshotSettings $snapshotSettings
     */
    public function __construct(SnapshotSettings $snapshotSettings)
    {
        $this->snapshotSettings = $snapshotSettings;
    }

    /**
     * Creates snapshot only if it is enabled
     *
     * Snapshot is disabled for all entities except:
     * - ExportQueue
     * - InitialExportStatus
     *
     * Snapshot is only disabled during preparing Export queue
     *
     * @param Subject $subject
     * @param callable $proceed
     * @param DataObject $entity
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRegisterSnapshot(Subject $subject, callable $proceed, DataObject $entity)
    {
        if ($this->snapshotSettings->isEnabled()
            || ($entity instanceof ExportQueueInterface)
            || ($entity instanceof InitialExportStatusInterface)
        ) {
            $proceed($entity);
        }
    }
}
