<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
declare(strict_types=1);

namespace Bloomreach\EngagementConnector\Registry;

/**
 * Export queue registry to store data during entity exporting
 */
class ExportQueue
{
    /**
     * @var int
     */
    private $newItemsCount = 0;

    /**
     * Get new items count
     *
     * @return int
     */
    public function getNewItemsCount(): int
    {
        return $this->newItemsCount;
    }

    /**
     * Add provided value to the new items count
     *
     * @param int $count
     *
     * @return void
     */
    public function addToNewItemsCount(int $count): void
    {
        $this->newItemsCount += $count;
    }
}
