<?php
/**
 * @author Bloomreach
 * @copyright Copyright (c) Bloomreach (https://www.bloomreach.com/)
 */
namespace Bloomreach\EngagementConnector\Model\Export\Queue\Batch\Command\Data\Builder;

use Bloomreach\EngagementConnector\Api\Data\ExportQueueInterface;

/**
 * Builds command data
 */
interface BuilderInterface
{
    /**
     * Builds command data
     *
     * @param ExportQueueInterface $exportQueue
     *
     * @return array
     */
    public function build(ExportQueueInterface $exportQueue): array;
}
