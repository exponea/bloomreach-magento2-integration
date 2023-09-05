<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api;

/**
 * Delete export queue item
 */
interface DeleteExportQueueInterface
{
    /**
     * Delete export queue item
     *
     * @param int $entityId
     * @return void
     */
    public function execute(int $entityId): void;
}
