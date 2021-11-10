<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Api;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Saves export queue
 */
interface SaveExportQueueInterface
{
    /**
     * Saves export queue
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return ExportQueueInterface
     * @throws CouldNotSaveException
     */
    public function execute(ExportQueueInterface $exportQueue): ExportQueueInterface;
}
