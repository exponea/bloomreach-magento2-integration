<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\Export\Transporter;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;

/**
 * Sends data to the Bloomreach service
 */
interface TransporterInterface
{
    /**
     * Sends data to the Bloomreach service
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return bool
     */
    public function send(ExportQueueInterface $exportQueue): bool;
}
