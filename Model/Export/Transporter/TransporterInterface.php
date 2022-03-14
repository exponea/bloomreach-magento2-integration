<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\Export\Transporter;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * @throws LocalizedException
     */
    public function send(ExportQueueInterface $exportQueue): bool;
}
