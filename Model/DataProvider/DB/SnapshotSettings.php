<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Model\DataProvider\DB;

/**
 * Snapshot Settings data provider
 */
class SnapshotSettings
{
    /**
     * @var bool
     */
    private $status = true;

    /**
     * Is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->status;
    }

    /**
     * Set Enabled
     *
     * @param bool $status
     *
     * @return void
     */
    public function setEnabled(bool $status): void
    {
        $this->status = $status;
    }
}
